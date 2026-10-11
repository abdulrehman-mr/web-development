<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use App\Support\ImageImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /** How many images one "import" request downloads, so requests stay well under PHP time limits. */
    private const BATCH = 4;

    public function index(Request $request, ImageImporter $importer)
    {
        $status = $request->query('status');

        return view('admin.media.index', [
            'assets' => MediaAsset::query()
                ->when(in_array($status, [MediaAsset::PENDING, MediaAsset::IMPORTED, MediaAsset::FAILED], true), fn ($q) => $q->where('status', $status))
                ->latest('id')->paginate(48)->withQueryString(),
            'stats' => $importer->stats(),
            'status' => $status,
        ]);
    }

    /** Add image URLs (one per line) to the library. */
    public function store(Request $request)
    {
        $request->validate(['urls' => 'required|string|max:50000']);

        $urls = collect(preg_split('/\s+/', $request->input('urls')))
            ->map(fn ($u) => trim($u))->filter(fn ($u) => (MediaAsset::isRemote($u) && filter_var($u, FILTER_VALIDATE_URL)) || MediaAsset::isWikiReference($u))->unique();

        $urls->each(fn ($u) => MediaAsset::register($u));

        return back()->with('status', $urls->count().' URL(s) added. Click “Import now” to download them.');
    }

    /** Find remote images used anywhere in the site content. */
    public function scan(ImageImporter $importer)
    {
        $new = $importer->scan();

        return back()->with('status', "Scan complete — {$new} new remote image(s) found.");
    }

    /**
     * Download the next batch. Called repeatedly by the admin page until
     * nothing remains, so large libraries import without timeouts.
     */
    public function import(Request $request, ImageImporter $importer)
    {
        @set_time_limit(300);
        $retry = $request->boolean('retry_failed');

        if ($request->boolean('first')) {
            $importer->scan();
        }

        $skip = collect($request->input('skip', []))->map(fn ($id) => (int) $id);
        $batch = $importer->queue($retry)->reject(fn ($a) => $skip->contains($a->id))->take(self::BATCH);

        $results = $batch->map(fn (MediaAsset $a) => [
            'id' => $a->id,
            'url' => $a->source_url,
            'ok' => $importer->download($a),
            'error' => $a->error,
        ])->values();

        $remaining = $importer->queue($retry)->reject(fn ($a) => $skip->contains($a->id) || $batch->contains('id', $a->id))
            ->reject(fn ($a) => $a->status === MediaAsset::FAILED && ! $retry)->count();

        return response()->json([
            'results' => $results,
            'remaining' => $remaining,
            'localized' => $remaining === 0 ? $importer->localize() : 0,
            'stats' => $importer->stats(),
        ]);
    }

    public function destroy(MediaAsset $medium)
    {
        if ($medium->path) {
            Storage::disk('public')->delete($medium->path);
        }
        $medium->delete();

        return back()->with('status', 'Image removed from the library.');
    }
}
