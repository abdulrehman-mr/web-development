<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\MediaAsset;
use App\Models\Package;
use App\Models\Setting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Copies remote images used by the site onto the public disk and points the
 * content at the local copy, so the live site never depends on a third-party host.
 *
 *  1. scan()     – register every remote image URL found in site content
 *  2. download() – fetch pending / missing files
 *  3. localize() – swap remote URLs in the content for local paths
 */
class ImageImporter
{
    public const MAX_BYTES = 20 * 1024 * 1024;

    private const EXTENSIONS = [
        'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp',
        'image/gif' => 'gif', 'image/avif' => 'avif', 'image/svg+xml' => 'svg',
    ];

    /** Every image value used by the site, as [model or 'setting', column, value]. */
    private function imageFields(): iterable
    {
        foreach (Category::all() as $m) {
            yield [$m, 'image'];
        }
        foreach (Destination::all() as $m) {
            yield [$m, 'image'];
        }
        foreach (Hotel::all() as $m) {
            yield [$m, 'image'];
        }
        foreach (Package::all() as $m) {
            yield [$m, 'hero_image'];
            yield [$m, 'gallery'];
        }
    }

    private function settingImageKeys(): array
    {
        return collect(Setting::SCHEMA)->flatMap(fn ($fields) => collect($fields)->filter(fn ($f) => $f[1] === 'image')->keys())->all();
    }

    /** Register remote URLs found in site content. Returns how many are new. */
    public function scan(): int
    {
        $before = MediaAsset::count();

        foreach ($this->imageFields() as [$model, $column]) {
            foreach ((array) $model->{$column} as $value) {
                if (MediaAsset::isRemote($value)) {
                    MediaAsset::register($value);
                }
            }
        }
        foreach ($this->settingImageKeys() as $key) {
            if (MediaAsset::isRemote($value = setting($key))) {
                MediaAsset::register($value);
            }
        }

        return MediaAsset::count() - $before;
    }

    /** Assets that need downloading: pending, failed (if retrying), or imported but missing on disk. */
    public function queue(bool $retryFailed = false)
    {
        return MediaAsset::query()->orderBy('id')->get()->filter(fn (MediaAsset $a) => match ($a->status) {
            MediaAsset::PENDING => true,
            MediaAsset::FAILED => $retryFailed,
            default => ! $a->fileExists(),
        })->values();
    }

    public function download(MediaAsset $asset): bool
    {
        $asset->increment('attempts');

        try {
            $response = Http::withUserAgent(config('app.name').'-ImageImporter/1.0 (+'.config('app.url').')')
                ->accept('image/*')
                ->timeout(60)
                ->retry(2, 1000, fn ($e) => $e instanceof ConnectionException || ($e instanceof RequestException && $e->response->serverError()), throw: false)
                ->get($asset->source_url);

            if (! $response->successful()) {
                throw new \RuntimeException('HTTP '.$response->status());
            }

            $mime = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));
            $extension = self::EXTENSIONS[$mime] ?? null;
            if (! $extension) {
                throw new \RuntimeException("Not an image ({$mime})");
            }

            $body = $response->body();
            if (strlen($body) > self::MAX_BYTES) {
                throw new \RuntimeException('File is larger than 20 MB');
            }

            $path = 'media/'.$asset->source_hash.'.'.$extension;
            Storage::disk('public')->put($path, $body);

            $asset->update([
                'path' => $path, 'status' => MediaAsset::IMPORTED, 'error' => null,
                'mime' => $mime, 'bytes' => strlen($body), 'imported_at' => now(),
            ]);

            return true;
        } catch (Throwable $e) {
            $asset->update(['status' => MediaAsset::FAILED, 'error' => mb_substr($e->getMessage(), 0, 500)]);

            return false;
        }
    }

    /** Replace remote URLs in site content with local paths of imported assets. Returns fields changed. */
    public function localize(): int
    {
        $map = MediaAsset::where('status', MediaAsset::IMPORTED)->get()
            ->filter(fn (MediaAsset $a) => $a->fileExists())
            ->mapWithKeys(fn (MediaAsset $a) => [$a->source_url => $a->path]);

        if ($map->isEmpty()) {
            return 0;
        }

        $changed = 0;
        foreach ($this->imageFields() as [$model, $column]) {
            $value = $model->{$column};
            $new = is_array($value)
                ? array_map(fn ($v) => $map[$v] ?? $v, $value)
                : ($map[$value] ?? $value);

            if ($new !== $value) {
                $model->forceFill([$column => $new])->saveQuietly();
                $changed++;
            }
        }

        $settings = [];
        foreach ($this->settingImageKeys() as $key) {
            if (isset($map[$value = setting($key)])) {
                $settings[$key] = $map[$value];
            }
        }
        if ($settings) {
            Setting::put($settings);
            $changed += count($settings);
        }

        return $changed;
    }

    public function stats(): array
    {
        $counts = MediaAsset::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return [
            'pending' => (int) ($counts[MediaAsset::PENDING] ?? 0),
            'imported' => (int) ($counts[MediaAsset::IMPORTED] ?? 0),
            'failed' => (int) ($counts[MediaAsset::FAILED] ?? 0),
            'missing' => $this->queue()->where('status', MediaAsset::IMPORTED)->count(),
        ];
    }
}
