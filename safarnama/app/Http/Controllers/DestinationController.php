<?php

namespace App\Http\Controllers;

use App\Models\Destination;

class DestinationController extends Controller
{
    public function index()
    {
        $destinations = Destination::withCount(['packages' => fn ($q) => $q->published()])->orderBy('sort_order')->get();

        return view('destinations.index', [
            'regions' => $destinations->groupBy('region'),
            'count' => $destinations->count(),
        ]);
    }

    public function show(Destination $destination)
    {
        $destination->load(['packages' => fn ($q) => $q->published()->with('category'), 'hotels']);

        return view('destinations.show', [
            'destination' => $destination,
            'nearby' => Destination::where('region', $destination->region)->where('id', '!=', $destination->id)->take(3)->get(),
        ]);
    }
}
