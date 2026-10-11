<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\Package;
use App\Models\Testimonial;
use App\Models\Vibe;

class HomeController extends Controller
{
    public function __invoke()
    {
        $packages = Package::published()->with('category')->orderBy('sort_order')->get();

        return view('home', [
            'categories' => Category::withCount(['packages' => fn ($q) => $q->published()])->orderBy('sort_order')->get()
                ->filter(fn ($c) => $c->packages_count > 0)->take(9)->values(),
            'vibes' => Vibe::withCount(['packages' => fn ($q) => $q->published()])->orderBy('sort_order')->get()->filter(fn ($v) => $v->packages_count > 0)->values(),
            'featured' => $packages->where('is_featured', true)->take(4)->values(),
            'yatra' => $packages->first(fn ($p) => $p->category?->slug === 'sikh-yatra'),
            'destinations' => Destination::where('is_featured', true)->orderBy('sort_order')->take(6)->get(),
            'searchDestinations' => Destination::orderBy('name')->get(['name', 'slug', 'country'])->groupBy('country_name')->sortKeys(),
            'testimonials' => Testimonial::where('is_published', true)->with('package')->orderBy('sort_order')->get(),
            'faqs' => Faq::where('is_published', true)->orderBy('sort_order')->get(),
        ]);
    }
}
