<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A remote image that is (or will be) copied onto the public disk.
 * The source URL is kept so a missing file can always be downloaded again.
 */
class MediaAsset extends Model
{
    public const PENDING = 'pending';

    public const IMPORTED = 'imported';

    public const FAILED = 'failed';

    protected $fillable = ['source_hash', 'source_url', 'path', 'status', 'error', 'mime', 'bytes', 'attempts', 'imported_at'];

    protected function casts(): array
    {
        return ['imported_at' => 'datetime'];
    }

    public static function isRemote(?string $value): bool
    {
        return is_string($value) && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://'));
    }

    public static function register(string $url): self
    {
        return static::firstOrCreate(['source_hash' => sha1($url)], ['source_url' => $url]);
    }

    public function fileExists(): bool
    {
        return $this->path && Storage::disk('public')->exists($this->path);
    }

    public function getUrlAttribute(): string
    {
        return $this->fileExists() ? Storage::disk('public')->url($this->path) : $this->source_url;
    }
}
