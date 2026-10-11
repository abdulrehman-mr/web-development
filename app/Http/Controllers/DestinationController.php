<?php

namespace App\Http\Controllers;

use App\Models\Destination;

class DestinationController extends Controller
{
    public function index()
    {
        $destinations = Destination::withCount(['packages' => fn ($q) => $q->published()])->orderBy('sort_order')->get();

        $multipleCountries = $destinations->pluck('country')->unique()->count() > 1;

        return view('destinations.index', [
            'regions' => $multipleCountries
                ? $destinations->groupBy('country_name')->sortKeys()
                : $destinations->groupBy(fn ($d) => $d->region ?: $d->country_name),
            'multipleCountries' => $multipleCountries,
            'count' => $destinations->count(),
        ]);
    }

    public function show(Destination $destination)
    {
        $destination->load(['packages' => fn ($q) => $q->published()->with('category'), 'hotels']);

        return view('destinations.show', [
            'destination' => $destination,
            'nearby' => Destination::where('country', $destination->country)->where('id', '!=', $destination->id)
                ->orderByRaw('region = ? desc', [$destination->region])->take(3)->get(),
        ]);
    }
}
