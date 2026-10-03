<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HotelController extends Controller
{
    use Concerns;

    public function index()
    {
        return view('admin.hotels.index', ['hotels' => Hotel::with('destination')->orderBy('name')->get()]);
    }

    public function create()
    {
        return $this->form(new Hotel(['stars' => 4, 'type' => 'Boutique hotel']));
    }

    public function store(Request $request)
    {
        $this->save($request, new Hotel);

        return redirect()->route('admin.hotels.index')->with('status', 'Stay created.');
    }

    public function edit(Hotel $hotel)
    {
        return $this->form($hotel);
    }

    public function update(Request $request, Hotel $hotel)
    {
        $this->save($request, $hotel);

        return redirect()->route('admin.hotels.index')->with('status', 'Stay saved.');
    }

    public function destroy(Hotel $hotel)
    {
        $hotel->delete();

        return back()->with('status', 'Stay deleted.');
    }

    private function form(Hotel $hotel)
    {
        return view('admin.hotels.form', ['hotel' => $hotel, 'destinations' => Destination::orderBy('name')->get()]);
    }

    private function save(Request $request, Hotel $hotel): void
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'destination_id' => 'nullable|exists:destinations,id',
            'type' => ['required', Rule::in(Hotel::TYPES)],
            'stars' => 'required|integer|min:1|max:5',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'amenities' => 'nullable|string',
        ] + $this->imageRules('image'));

        $hotel->fill([
            'name' => $data['name'],
            'slug' => $hotel->slug ?: $this->uniqueSlug(Hotel::class, $data['name']),
            'destination_id' => $data['destination_id'] ?? null,
            'type' => $data['type'],
            'stars' => $data['stars'],
            'description' => $data['description'] ?? null,
            'address' => $data['address'] ?? null,
            'amenities' => $this->lines($data['amenities'] ?? ''),
            'image' => $this->imageFrom($request, 'image', $hotel->image),
        ])->save();
    }
}
