<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\MediaAsset;
use App\Models\Package;
use App\Models\RoomType;
use App\Models\Vibe;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        return $this->form(new Package(['is_published' => true, 'duration_days' => 7, 'difficulty' => 'Easy', 'rating' => 5, 'country' => 'PK']));
    }

    public function store(Request $request)
    {
        $package = DB::transaction(fn () => $this->save($request, new Package));

        return redirect()->route('admin.packages.edit', $package)->with('status', 'Journey created.');
    }

    public function edit(Package $package)
    {
        return $this->form($package->load('days', 'destinations', 'vibes'));
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
            'roomTypes' => RoomType::active()->get(),
            'vibes' => Vibe::orderBy('sort_order')->get(),
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
            'date_mode' => 'nullable|in:any,departures,both',
            'departures' => 'nullable|array|max:400',
            'departures.*.id' => 'nullable|integer',
            'departures.*.starts_on' => 'required|date',
            'departures.*.seats' => 'nullable|integer|min:1|max:10000',
            'departures.*.price_adjustment' => 'nullable|numeric|min:-1000000|max:1000000',
            'departures.*.label' => 'nullable|string|max:120',
            'departures.*.is_closed' => 'nullable|boolean',
            'days.*.activities' => 'nullable|array|max:12',
            'days.*.activities.*.time' => 'nullable|string|max:40',
            'days.*.activities.*.title' => 'nullable|string|max:200',
            'days.*.activities.*.description' => 'nullable|string|max:2000',
            'days.*.activities.*.image' => 'nullable|string|max:2048',
            'days.*.activities.*.image_file' => 'nullable|image|max:8192',
            'child_price' => 'nullable|numeric|min:0',
            'tiers' => 'nullable|array',
            'tiers.*.min' => 'required|integer|min:2|max:200|distinct',
            'tiers.*.price' => 'required|numeric|min:0',
            'rooms' => 'nullable|array',
            'rooms.*.enabled' => 'nullable|boolean',
            'rooms.*.adjustment' => 'nullable|numeric|min:-100000|max:100000',
            'default_room_type_id' => 'nullable|exists:room_types,id',
            'extras' => 'nullable|array',
            'extras.*.name' => 'required|string|max:120',
            'extras.*.description' => 'nullable|string|max:255',
            'extras.*.price' => 'required|numeric|min:-100000|max:100000',
            'extras.*.per' => 'required|in:person,booking',
            'country' => ['nullable', Rule::in(array_keys(config('countries')))],
            'vibe_ids' => 'nullable|array',
            'vibe_ids.*' => 'exists:vibes,id',
            'groups' => 'nullable|array',
            'groups.*.name' => 'required|string|max:80',
            'groups.*.description' => 'nullable|string|max:255',
            'groups.*.options' => 'required|array|min:1',
            'groups.*.options.*.label' => 'nullable|string|max:120',
            'groups.*.options.*.description' => 'nullable|string|max:255',
            'groups.*.options.*.hotel_id' => 'nullable|exists:hotels,id',
            'groups.*.options.*.price' => 'required|numeric|min:-100000|max:100000',
            'groups.*.options.*.per' => 'required|in:adult,person,booking',
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
            'date_mode' => $data['date_mode'] ?? 'any',
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
            'country' => $data['country'] ?? $package->country
                ?? Destination::whereIn('id', $data['destination_ids'] ?? [])->value('country') ?? 'PK',
            'option_groups' => collect($data['groups'] ?? [])->map(fn ($g) => [
                'name' => $g['name'], 'description' => $g['description'] ?? null,
                'options' => collect($g['options'])->filter(fn ($o) => filled($o['label'] ?? null) || filled($o['hotel_id'] ?? null))->map(fn ($o) => [
                    'label' => $o['label'] ?? null, 'description' => $o['description'] ?? null,
                    'hotel_id' => isset($o['hotel_id']) ? (int) $o['hotel_id'] : null, 'price' => (float) $o['price'], 'per' => $o['per'],
                ])->values()->all(),
            ])->filter(fn ($g) => $g['options'] !== [])->values()->all(),
            'child_price' => $data['child_price'] ?? null,
            'group_tiers' => collect($data['tiers'] ?? [])->map(fn ($t) => ['min' => (int) $t['min'], 'price' => (float) $t['price']])->sortBy('min')->values()->all(),
            'room_options' => collect($data['rooms'] ?? [])->map(fn ($r) => [
                'enabled' => ! empty($r['enabled']),
                'adjustment' => isset($r['adjustment']) && $r['adjustment'] !== '' ? (float) $r['adjustment'] : null,
            ])->all() ?: null,
            'default_room_type_id' => $data['default_room_type_id'] ?? null,
            'extras' => collect($data['extras'] ?? [])->map(fn ($e) => [
                'name' => $e['name'], 'description' => $e['description'] ?? null, 'price' => (float) $e['price'], 'per' => $e['per'],
            ])->values()->all(),
        ])->save();

        $package->destinations()->sync($data['destination_ids'] ?? []);
        $package->vibes()->sync($data['vibe_ids'] ?? []);

        $this->syncDepartures($package, $data['departures'] ?? []);

        $package->days()->delete();
        foreach (array_values($data['days'] ?? []) as $i => $day) {
            $package->days()->create([
                'day_number' => $i + 1,
                'title' => $day['title'],
                'description' => $day['description'] ?? null,
                'meals' => $day['meals'] ?? null,
                'hotel_id' => $day['hotel_id'] ?? null,
                'activities' => $this->activities($day['activities'] ?? []) ?: null,
            ]);
        }

        return $package;
    }

    /**
     * A day's timed activities. A photo can be uploaded or given as a URL; empty rows are dropped.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return list<array{time: ?string, title: string, description: ?string, image: ?string}>
     */
    private function activities(array $rows): array
    {
        return collect($rows)->map(function (array $row) {
            $image = ($row['image_file'] ?? null) instanceof UploadedFile
                ? $row['image_file']->store('uploads', 'public')
                : (filled($row['image'] ?? null) ? $row['image'] : null);
            if ($image && MediaAsset::isImportable($image)) {
                MediaAsset::register($image);
            }

            return [
                'time' => filled($row['time'] ?? null) ? $row['time'] : null,
                'title' => (string) ($row['title'] ?? ''),
                'description' => filled($row['description'] ?? null) ? $row['description'] : null,
                'image' => $image,
            ];
        })->filter(fn ($a) => $a['title'] !== '' || $a['image'])->values()->all();
    }

    /**
     * Save the departure list. A removed departure that already has bookings is closed instead of deleted.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncDepartures(Package $package, array $rows): void
    {
        $kept = [];
        foreach ($rows as $row) {
            $values = [
                'starts_on' => $row['starts_on'],
                'seats' => filled($row['seats'] ?? null) ? (int) $row['seats'] : null,
                'price_adjustment' => (float) ($row['price_adjustment'] ?? 0),
                'label' => filled($row['label'] ?? null) ? $row['label'] : null,
                'is_closed' => (bool) ($row['is_closed'] ?? false),
            ];
            $departure = filled($row['id'] ?? null) ? $package->departures()->find($row['id']) : null;
            $departure ? $departure->update($values) : $departure = $package->departures()->create($values);
            $kept[] = $departure->id;
        }

        foreach ($package->departures()->whereNotIn('id', $kept)->withCount('bookings')->get() as $removed) {
            $removed->bookings_count > 0 ? $removed->update(['is_closed' => true]) : $removed->delete();
        }
    }
}
