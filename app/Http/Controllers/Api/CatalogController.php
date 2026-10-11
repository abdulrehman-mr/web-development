<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\Hotel;
use App\Models\Package;
use App\Models\Setting;
use App\Models\Testimonial;
use App\Models\Vibe;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /** Public site settings (contact details, header and footer text) — never anything secret. */
    public function settings()
    {
        $keys = collect(Setting::SCHEMA)->flatMap(fn ($fields) => array_keys($fields));

        return ['data' => $keys->mapWithKeys(fn ($k) => [$k => str_ends_with($k, 'image') || $k === 'logo' ? media_url(setting($k)) : setting($k)])];
    }

    public function categories()
    {
        return ['data' => Category::orderBy('sort_order')->get()->map(fn ($c) => $c->only('id', 'name', 'slug', 'tagline', 'description') + ['image' => $c->image_url])];
    }

    public function destinations()
    {
        return ['data' => Destination::orderBy('sort_order')->get()->map(fn ($d) => $this->destinationData($d))];
    }

    public function destination(Destination $destination)
    {
        $destination->load(['packages' => fn ($q) => $q->published(), 'hotels']);

        return ['data' => $this->destinationData($destination) + [
            'packages' => $destination->packages->map(fn ($p) => $this->packageSummary($p)),
            'hotels' => $destination->hotels->map(fn ($h) => $this->hotelData($h)),
        ]];
    }

    public function packages(Request $request)
    {
        $packages = Package::published()->with('category', 'vibes')
            ->when($request->query('category'), fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($request->boolean('featured'), fn ($q) => $q->where('is_featured', true))
            ->when($request->query('country'), fn ($q, $code) => $q->where('country', strtoupper($code)))
            ->when($request->query('vibe'), fn ($q, $slug) => $q->whereHas('vibes', fn ($v) => $v->where('slug', $slug)))
            ->orderBy('sort_order')
            ->paginate(min((int) $request->query('per_page', 12), 50));

        return $packages->through(fn ($p) => $this->packageSummary($p));
    }

    public function package(Package $package)
    {
        abort_unless($package->is_published, 404);
        $package->load(['category', 'destinations', 'days.hotel', 'vibes']);

        return ['data' => $this->packageSummary($package) + [
            'overview' => $package->overview,
            'gallery' => $package->gallery_urls,
            'highlights' => $package->highlights ?? [],
            'inclusions' => $package->inclusions ?? [],
            'exclusions' => $package->exclusions ?? [],
            'destinations' => $package->destinations->map(fn ($d) => $d->only('name', 'slug', 'region')),
            'itinerary' => $package->days->map(fn ($day) => [
                'day' => $day->day_number,
                'title' => $day->title,
                'description' => $day->description,
                'meals' => $day->meals,
                'stay' => $day->hotel ? $this->hotelData($day->hotel) : null,
                'activities' => collect($day->activities ?? [])->map(fn ($a) => ['image' => media_url($a['image'])] + $a)->values(),
            ]),
        ]];
    }

    public function hotels()
    {
        return ['data' => Hotel::with('destination')->orderBy('name')->get()->map(fn ($h) => $this->hotelData($h))];
    }

    public function vibes()
    {
        return ['data' => Vibe::orderBy('sort_order')->get(['name', 'slug', 'description', 'icon'])];
    }

    public function testimonials()
    {
        return ['data' => Testimonial::where('is_published', true)->orderBy('sort_order')->get(['name', 'location', 'quote', 'rating'])];
    }

    public function faqs()
    {
        return ['data' => Faq::where('is_published', true)->orderBy('sort_order')->get(['question', 'answer'])];
    }

    private function packageSummary(Package $p): array
    {
        return [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'url' => route('packages.show', $p),
            'category' => $p->category?->only('name', 'slug'),
            'country' => $p->country,
            'vibes' => $p->vibes->map(fn ($v) => $v->only('name', 'slug'))->values(),
            'summary' => $p->summary,
            'duration_days' => $p->duration_days,
            'price' => (float) $p->price,
            'original_price' => $p->original_price ? (float) $p->original_price : null,
            'currency' => currencies()->base()->code,
            'group_size' => $p->group_size,
            'difficulty' => $p->difficulty,
            'best_season' => $p->best_season,
            'start_city' => $p->start_city,
            'rating' => (float) $p->rating,
            'is_featured' => $p->is_featured,
            'image' => $p->hero_image_url,
        ];
    }

    private function destinationData(Destination $d): array
    {
        return $d->only('id', 'name', 'slug', 'country', 'region', 'tagline', 'description', 'highlights', 'best_season', 'is_featured') + ['image' => $d->image_url];
    }

    private function hotelData(Hotel $h): array
    {
        return $h->only('id', 'name', 'type', 'stars', 'description', 'amenities', 'address') + [
            'destination' => $h->destination?->name,
            'image' => $h->image_url,
        ];
    }
}
