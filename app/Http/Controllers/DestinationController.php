<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Page;
use App\Support\PageBuilder;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(PageBuilder $builder): View
    {
        return $builder->view(Page::system('destinations'));
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
