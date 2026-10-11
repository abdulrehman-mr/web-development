<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Package;
use App\Models\Page;
use App\Models\Vibe;
use App\Support\PageBuilder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(Request $request, PageBuilder $builder): View
    {
        // The hero search sends one "where" field: a destination slug or "country:FR".
        if ($where = (string) $request->query('where')) {
            $request->merge(str_starts_with($where, 'country:') ? ['country' => substr($where, 8)] : ['destination' => $where]);
        }

        $filters = $request->validate([
            'country' => 'nullable|string|size:2',
            'vibe' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'destination' => 'nullable|string|max:100',
            'duration' => 'nullable|in:short,medium,long',
            'sort' => 'nullable|in:featured,price_asc,price_desc,duration',
            'q' => 'nullable|string|max:100',
        ]);

        $packages = Package::published()
            ->with(['category', 'destinations', 'vibes'])
            ->when($filters['country'] ?? null, fn ($q, $code) => $q->where('country', strtoupper($code)))
            ->when($filters['vibe'] ?? null, fn ($q, $slug) => $q->whereHas('vibes', fn ($v) => $v->where('slug', $slug)))
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

        $activeCategory = Category::where('slug', $filters['category'] ?? null)->first();
        $activeVibe = Vibe::where('slug', $filters['vibe'] ?? null)->first();
        $activeCountry = isset($filters['country']) ? (config('countries')[strtoupper($filters['country'])] ?? null) : null;

        // A filtered list retitles the page's hero, e.g. "Romantic" or "Journeys in France".
        $heroOverride = ($activeCategory || $activeVibe || $activeCountry) ? [
            'eyebrow' => $activeCategory ? 'Experience' : ($activeVibe ? 'Vibe' : 'Country'),
            'title' => $activeCategory?->name ?? $activeVibe?->name ?? 'Journeys in '.$activeCountry,
            'subtitle' => $activeCategory?->description ?? $activeVibe?->description,
            'image' => $activeCategory?->image,
        ] : [];

        return $builder->view(Page::system('journeys'), [
            'packages' => $packages,
            'filters' => $filters,
            'categories' => Category::orderBy('sort_order')->get(),
            'destinations' => Destination::orderBy('name')->get(),
            'countries' => self::countriesWithJourneys(),
            'vibes' => Vibe::whereHas('packages', fn ($q) => $q->published())->orderBy('sort_order')->get(),
            'heroOverride' => $heroOverride,
            'title' => $heroOverride['title'] ?? null,
            'description' => $heroOverride['subtitle'] ?? null,
        ]);
    }

    /** @return array<string, string> code => name, for countries that have published journeys */
    public static function countriesWithJourneys(): array
    {
        return Package::published()->whereNotNull('country')->distinct()->pluck('country')
            ->mapWithKeys(fn ($code) => [$code => config('countries')[$code] ?? $code])->sort()->all();
    }

    public function show(Package $package)
    {
        abort_unless($package->is_published, 404);

        $package->load(['category', 'destinations', 'vibes', 'days.hotel.destination', 'testimonials' => fn ($q) => $q->where('is_published', true)]);

        return view('packages.show', [
            'package' => $package,
            'related' => Package::published()->where('id', '!=', $package->id)
                ->orderByRaw('category_id = ? desc', [$package->category_id])
                ->orderBy('sort_order')->take(3)->get(),
        ]);
    }
}
