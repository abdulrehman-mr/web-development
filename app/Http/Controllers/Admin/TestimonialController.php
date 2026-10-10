<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        return view('admin.testimonials.index', ['testimonials' => Testimonial::with('package')->orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return $this->form(new Testimonial(['rating' => 5, 'is_published' => true]));
    }

    public function store(Request $request)
    {
        Testimonial::create($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Review added.');
    }

    public function edit(Testimonial $testimonial)
    {
        return $this->form($testimonial);
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $testimonial->update($this->validated($request));

        return redirect()->route('admin.testimonials.index')->with('status', 'Review saved.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return back()->with('status', 'Review deleted.');
    }

    private function form(Testimonial $testimonial)
    {
        return view('admin.testimonials.form', ['testimonial' => $testimonial, 'packages' => Package::orderBy('title')->get()]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:120',
            'location' => 'nullable|string|max:120',
            'quote' => 'required|string|max:2000',
            'rating' => 'required|integer|min:1|max:5',
            'package_id' => 'nullable|exists:packages,id',
            'sort_order' => 'nullable|integer',
        ]) + ['is_published' => $request->boolean('is_published')];
    }
}
