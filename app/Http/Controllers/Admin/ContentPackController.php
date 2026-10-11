<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ContentPacks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentPackController extends Controller
{
    public function index(ContentPacks $packs): View
    {
        return view('admin.content-packs', [
            'packs' => $packs->all()->map(fn (array $pack) => $pack + ['imported' => $packs->importedCounts($pack['key'])]),
        ]);
    }

    public function import(Request $request, ContentPacks $packs, string $pack): RedirectResponse
    {
        abort_unless($packs->find($pack), 404);
        $created = $packs->import($pack, $request->boolean('publish'));

        $message = "Imported {$created['packages']} journeys, {$created['destinations']} destinations and {$created['hotels']} hotels.";
        if ($created['images'] > 0) {
            $message .= " {$created['images']} images are queued: open Media library → Import now (or they download automatically within 15 minutes when the scheduler runs).";
        }

        return back()->with('status', $message);
    }

    public function destroy(ContentPacks $packs, string $pack): RedirectResponse
    {
        abort_unless($packs->find($pack), 404);
        $result = $packs->remove($pack);

        return back()->with('status', "Removed {$result['removed']} items.".($result['kept'] ? " {$result['kept']} journeys with bookings were kept and unpublished." : ''));
    }
}
