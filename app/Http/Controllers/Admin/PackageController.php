<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\MediaAsset;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    use Concerns;

    public function index(Request $request)
    {
        $packages = Package::with('category')->withCount('bookings')
            ->when($request->query('q'), fn ($q, $t) => $q->where('title', 'like', "%{$t}%"))
            ->orderBy('sort_order')->orderBy('title')->paginate(20)->withQueryString();

        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        return $this->form(new Package(['is_published' => true, 'duration_days' => 7, 'difficulty' => 'Easy', 'rating' => 5]));
    }

    public function store(Request $request)
    {
        $package = DB::transaction(fn () => $this->save($request, new Package));

        return redirect()->route('admin.packages.edit', $package)->with('status', 'Journey created.');
    }

    public function edit(Package $package)
    {
        return $this->form($package->load('days', 'destinations'));
    }

    public function update(Request $request, Package $package)
    {
        DB::transaction(fn () => $this->save($request, $package));

        return redirect()->route('admin.packages.edit', $package)->with('status', 'Journey saved.');
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('admin.packages.index')->with('status', 'Journey deleted.');
    }

    private function form(Package $package)
    {
        return view('admin.packages.form', [
            'package' => $package,
            'categories' => Category::orderBy('sort_order')->get(),
            'destinations' => Destination::orderBy('name')->get(),
            'hotels' => Hotel::with('destination')->orderBy('name')->get(),
        ]);
    }

    private function save(Request $request, Package $package): Package
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'slug' => ['nullable', 'string', 'max:200', 'alpha_dash', Rule::unique('packages')->ignore($package->id)],
            'category_id' => 'nullable|exists:categories,id',
            'summary' => 'nullable|string|max:500',
            'overview' => 'nullable|string',
            'duration_days' => 'required|integer|min:1|max:60',
            'price' => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
            'group_size' => 'nullable|string|max:100',
            'difficulty' => ['required', Rule::in(Package::DIFFICULTIES)],
            'best_season' => 'nullable|string|max:100',
            'start_city' => 'nullable|string|max:100',
            'rating' => 'nullable|numeric|min:0|max:5',
            'sort_order' => 'nullable|integer',
            'highlights' => 'nullable|string',
            'inclusions' => 'nullable|string',
            'exclusions' => 'nullable|string',
            'gallery' => 'nullable|string',
            'gallery_files.*' => 'nullable|image|max:8192',
            'destination_ids' => 'nullable|array',
            'destination_ids.*' => 'exists:destinations,id',
            'days' => 'nullable|array',
            'days.*.title' => 'required|string|max:200',
            'days.*.description' => 'nullable|string',
            'days.*.meals' => 'nullable|string|max:100',
            'days.*.hotel_id' => 'nullable|exists:hotels,id',
        ] + $this->imageRules('hero_image'));

        $gallery = $this->lines($data['gallery'] ?? '');
        foreach ($request->file('gallery_files', []) as $file) {
            $gallery[] = $file->store('uploads', 'public');
        }
        collect($gallery)->filter(fn ($g) => MediaAsset::isRemote($g))->each(fn ($g) => MediaAsset::register($g));

        $package->fill([
            'title' => $data['title'],
            'slug' => ($data['slug'] ?? null) ?: $this->uniqueSlug(Package::class, $data['title'], $package->id),
            'category_id' => $data['category_id'] ?? null,
            'summary' => $data['summary'] ?? null,
            'overview' => $data['overview'] ?? null,
            'duration_days' => $data['duration_days'],
            'price' => $data['price'],
            'original_price' => $data['original_price'] ?? null,
            'group_size' => $data['group_size'] ?? null,
            'difficulty' => $data['difficulty'],
            'best_season' => $data['best_season'] ?? null,
            'start_city' => $data['start_city'] ?? null,
            'rating' => $data['rating'] ?? 5,
            'sort_order' => $data['sort_order'] ?? 0,
            'hero_image' => $this->imageFrom($request, 'hero_image', $package->hero_image),
            'gallery' => $gallery,
            'highlights' => $this->lines($data['highlights'] ?? ''),
            'inclusions' => $this->lines($data['inclusions'] ?? ''),
            'exclusions' => $this->lines($data['exclusions'] ?? ''),
            'is_featured' => $request->boolean('is_featured'),
            'is_published' => $request->boolean('is_published'),
        ])->save();

        $package->destinations()->sync($data['destination_ids'] ?? []);

        $package->days()->delete();
        foreach (array_values($data['days'] ?? []) as $i => $day) {
            $package->days()->create([
                'day_number' => $i + 1,
                'title' => $day['title'],
                'description' => $day['description'] ?? null,
                'meals' => $day['meals'] ?? null,
                'hotel_id' => $day['hotel_id'] ?? null,
            ]);
        }

        return $package;
    }
}
