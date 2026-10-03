<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DestinationController extends Controller
{
    use Concerns;

    public function index()
    {
        return view('admin.destinations.index', [
            'destinations' => Destination::withCount('packages', 'hotels')->orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.destinations.form', ['destination' => new Destination(['region' => 'Punjab'])]);
    }

    public function store(Request $request)
    {
        $this->save($request, $destination = new Destination);

        return redirect()->route('admin.destinations.index')->with('status', 'Destination created.');
    }

    public function edit(Destination $destination)
    {
        return view('admin.destinations.form', compact('destination'));
    }

    public function update(Request $request, Destination $destination)
    {
        $this->save($request, $destination);

        return redirect()->route('admin.destinations.index')->with('status', 'Destination saved.');
    }

    public function destroy(Destination $destination)
    {
        $destination->delete();

        return back()->with('status', 'Destination deleted.');
    }

    private function save(Request $request, Destination $destination): void
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'region' => ['required', Rule::in(Destination::REGIONS)],
            'tagline' => 'nullable|string|max:200',
            'description' => 'nullable|string',
            'highlights' => 'nullable|string',
            'best_season' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer',
        ] + $this->imageRules('image'));

        $destination->fill([
            'name' => $data['name'],
            'slug' => $destination->slug ?: $this->uniqueSlug(Destination::class, $data['name']),
            'region' => $data['region'],
            'tagline' => $data['tagline'] ?? null,
            'description' => $data['description'] ?? null,
            'highlights' => $this->lines($data['highlights'] ?? ''),
            'best_season' => $data['best_season'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_featured' => $request->boolean('is_featured'),
            'image' => $this->imageFrom($request, 'image', $destination->image),
        ])->save();
    }
}
