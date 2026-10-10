<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'category' => 'nullable|string|max:100',
            'destination' => 'nullable|string|max:100',
            'duration' => 'nullable|in:short,medium,long',
            'sort' => 'nullable|in:featured,price_asc,price_desc,duration',
            'q' => 'nullable|string|max:100',
        ]);

        $packages = Package::published()
            ->with(['category', 'destinations'])
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($filters['destination'] ?? null, fn ($q, $slug) => $q->whereHas('destinations', fn ($d) => $d->where('slug', $slug)))
            ->when($filters['duration'] ?? null, fn ($q, $d) => match ($d) {
                'short' => $q->where('duration_days', '<=', 6),
                'medium' => $q->whereBetween('duration_days', [7, 9]),
                'long' => $q->where('duration_days', '>=', 10),
            })
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('title', 'like', "%{$term}%")
                ->orWhere('summary', 'like', "%{$term}%")))
            ->when($filters['sort'] ?? 'featured', fn ($q, $sort) => match ($sort) {
                'price_asc' => $q->orderBy('price'),
                'price_desc' => $q->orderByDesc('price'),
                'duration' => $q->orderBy('duration_days'),
                default => $q->orderByDesc('is_featured')->orderBy('sort_order'),
            })
            ->paginate(9)
            ->withQueryString();

        return view('packages.index', [
            'packages' => $packages,
            'filters' => $filters,
            'categories' => Category::orderBy('sort_order')->get(),
            'destinations' => Destination::orderBy('name')->get(),
            'activeCategory' => Category::where('slug', $filters['category'] ?? null)->first(),
        ]);
    }

    public function show(Package $package)
    {
        abort_unless($package->is_published, 404);

        $package->load(['category', 'destinations', 'days.hotel.destination', 'testimonials' => fn ($q) => $q->where('is_published', true)]);

        return view('packages.show', [
            'package' => $package,
            'related' => Package::published()->where('id', '!=', $package->id)
                ->orderByRaw('category_id = ? desc', [$package->category_id])
                ->orderBy('sort_order')->take(3)->get(),
        ]);
    }
}
