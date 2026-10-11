<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\ItineraryDay;
use App\Models\Package;
use App\Models\RoomType;
use App\Models\Vibe;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Ready-made country content (destinations, hotels, journeys with itineraries,
 * prices, vibes and images) stored in database/packs/*.php.
 *
 * Prices in packs are in US dollars and are converted to the base currency on import.
 * Images are "wiki:Article title" references that the image importer resolves and downloads.
 */
class ContentPacks
{
    /** @var array<int, string> */
    public const DEFAULT_INCLUSIONS = [
        'Airport meet-and-greet and private transfers',
        'Private air-conditioned vehicle with driver',
        'Licensed English-speaking guide',
        'Hotel accommodation as listed (twin sharing)',
        'Daily breakfast and meals as noted',
        'Entrance fees for the sights in the itinerary',
        '24/7 WhatsApp support during your trip',
    ];

    /** @var array<int, string> */
    public const DEFAULT_EXCLUSIONS = ['International flights', 'Visa fees', 'Travel insurance', 'Tips and personal expenses'];

    public function path(): string
    {
        return database_path('packs');
    }

    /** @return Collection<string, array<string, mixed>> */
    public function all(): Collection
    {
        return collect(glob($this->path().'/*.php'))
            ->map(fn (string $file) => require $file)
            ->filter(fn ($pack) => is_array($pack) && isset($pack['key'], $pack['country']))
            ->sortBy('name')
            ->keyBy('key');
    }

    /** @return array<string, mixed>|null */
    public function find(string $key): ?array
    {
        return $this->all()->get($key);
    }

    /** @return array{destinations: int, hotels: int, packages: int} */
    public function importedCounts(string $key): array
    {
        return [
            'destinations' => Destination::where('pack', $key)->count(),
            'hotels' => Hotel::where('pack', $key)->count(),
            'packages' => Package::where('pack', $key)->count(),
        ];
    }

    /**
     * Create everything in a pack. Items whose slug already exists are skipped,
     * so importing twice never duplicates or overwrites your edits.
     *
     * @return array{destinations: int, hotels: int, packages: int, images: int}
     */
    public function import(string $key, bool $publish = true): array
    {
        $pack = $this->find($key) ?? throw new InvalidArgumentException("Unknown content pack “{$key}”.");
        $toBase = $this->usdToBase();
        $created = ['destinations' => 0, 'hotels' => 0, 'packages' => 0, 'images' => 0];

        DB::transaction(function () use ($pack, $key, $publish, $toBase, &$created) {
            $destinations = [];
            foreach ($pack['destinations'] ?? [] as $i => $d) {
                $destination = Destination::firstOrCreate(['slug' => Str::slug($d['name'])], [
                    'name' => $d['name'], 'country' => $d['country'] ?? $pack['country'], 'region' => $d['region'] ?? '',
                    'tagline' => $d['tagline'] ?? null, 'description' => $d['description'] ?? null,
                    'highlights' => $d['highlights'] ?? [], 'best_season' => $d['best_season'] ?? null,
                    'image' => $d['image'] ?? null, 'is_featured' => $d['featured'] ?? false,
                    'sort_order' => 100 + $i, 'pack' => $key,
                ]);
                $created['destinations'] += (int) $destination->wasRecentlyCreated;
                $destinations[$d['name']] = $destination;
            }

            $hotels = [];
            foreach ($pack['hotels'] ?? [] as $h) {
                $hotel = Hotel::firstOrCreate(['slug' => Str::slug($h['name'])], [
                    'name' => $h['name'], 'destination_id' => ($destinations[$h['destination']] ?? null)?->id,
                    'type' => $h['type'] ?? 'Luxury hotel', 'stars' => $h['stars'] ?? 5,
                    'description' => $h['description'] ?? null, 'address' => $h['address'] ?? null,
                    'amenities' => $h['amenities'] ?? [], 'image' => $h['image'] ?? ($destinations[$h['destination']] ?? null)?->image,
                    'pack' => $key,
                ]);
                $created['hotels'] += (int) $hotel->wasRecentlyCreated;
                $hotels[$h['name']] = $hotel;
            }

            $roomTypes = RoomType::pluck('id', 'name');
            foreach ($pack['room_types'] ?? [] as $i => $r) {
                $roomTypes[$r['name']] ??= RoomType::create([
                    'name' => $r['name'], 'description' => $r['description'] ?? null,
                    'default_adjustment' => round(($r['adjustment'] ?? 0) * $toBase), 'sort_order' => 50 + $i, 'pack' => $key,
                ])->id;
            }

            $vibes = Vibe::pluck('id', 'name');
            foreach ($pack['packages'] ?? [] as $i => $p) {
                $slug = Str::slug($p['title']);
                if (Package::where('slug', $slug)->exists()) {
                    continue;
                }

                $category = Category::firstOrCreate(['slug' => Str::slug($p['category'])], [
                    'name' => $p['category'], 'tagline' => $p['category_tagline'] ?? null, 'pack' => $key,
                    'image' => $p['image'] ?? null, 'sort_order' => Category::max('sort_order') + 1,
                ]);

                $package = Package::create([
                    'category_id' => $category->id, 'country' => $p['country'] ?? $pack['country'], 'pack' => $key,
                    'title' => $p['title'], 'slug' => $slug, 'summary' => $p['summary'], 'overview' => $p['overview'] ?? null,
                    'duration_days' => count($p['itinerary']), 'price' => round($p['price'] * $toBase),
                    'original_price' => isset($p['original_price']) ? round($p['original_price'] * $toBase) : null,
                    'child_price' => isset($p['child_price']) ? round($p['child_price'] * $toBase) : null,
                    'group_tiers' => collect($p['tiers'] ?? [])->map(fn ($t) => ['min' => $t['min'], 'price' => round($t['price'] * $toBase)])->all(),
                    'extras' => collect($p['extras'] ?? [])->map(fn ($e) => [...$e, 'price' => round($e['price'] * $toBase)])->all(),
                    'room_options' => isset($p['rooms']) ? collect($p['rooms']['prices'])->mapWithKeys(fn ($price, $name) => [
                        $roomTypes[$name] => ['enabled' => true, 'adjustment' => round($price * $toBase)],
                    ])->all() : null,
                    'default_room_type_id' => isset($p['rooms']['default']) ? $roomTypes[$p['rooms']['default']] : null,
                    'option_groups' => collect($p['option_groups'] ?? [])->map(fn ($group) => [
                        'name' => $group['name'], 'description' => $group['description'] ?? null,
                        'options' => collect($group['options'])->map(fn ($o) => [
                            'label' => $o['label'] ?? null, 'description' => $o['description'] ?? null,
                            'hotel_id' => isset($o['hotel']) ? ($hotels[$o['hotel']] ?? null)?->id : null,
                            'price' => round($o['price'] * $toBase), 'per' => $o['per'] ?? 'adult',
                        ])->all(),
                    ])->all(),
                    'group_size' => $p['group_size'] ?? 'Private · 2–12 guests', 'difficulty' => $p['difficulty'] ?? 'Easy',
                    'best_season' => $p['best_season'] ?? null, 'start_city' => $p['start_city'] ?? null,
                    'hero_image' => $p['image'] ?? null,
                    'gallery' => collect($p['destinations'] ?? [])->map(fn ($name) => $destinations[$name]->image ?? null)->filter()->values()->all(),
                    'highlights' => $p['highlights'] ?? [],
                    'inclusions' => $p['inclusions'] ?? self::DEFAULT_INCLUSIONS,
                    'exclusions' => $p['exclusions'] ?? self::DEFAULT_EXCLUSIONS,
                    'is_featured' => $p['featured'] ?? false, 'is_published' => $publish,
                    'rating' => $p['rating'] ?? 4.9, 'sort_order' => 100 + $i,
                ]);

                $package->destinations()->sync(collect($p['destinations'] ?? [])->map(fn ($name) => $destinations[$name]->id ?? null)->filter());
                $package->vibes()->sync(collect($p['vibes'] ?? [])->map(fn ($name) => $vibes[$name] ?? null)->filter());

                foreach (array_values($p['itinerary']) as $n => [$title, $description, $hotel, $meals]) {
                    $package->days()->create([
                        'day_number' => $n + 1, 'title' => $title, 'description' => $description,
                        'hotel_id' => $hotel ? ($hotels[$hotel] ?? null)?->id : null, 'meals' => $meals,
                    ]);
                }
                $created['packages']++;
            }
        });

        $created['images'] = app(ImageImporter::class)->scan();

        return $created;
    }

    /**
     * Remove what a pack created. Journeys that already have bookings are kept
     * (unpublished) so no booking loses its journey.
     *
     * @return array{removed: int, kept: int}
     */
    public function remove(string $key): array
    {
        return DB::transaction(function () use ($key) {
            $packages = Package::where('pack', $key)->withCount('bookings')->get();
            [$kept, $removable] = $packages->partition(fn (Package $p) => $p->bookings_count > 0);

            Package::whereIn('id', $kept->pluck('id'))->update(['is_published' => false]);
            Package::whereIn('id', $removable->pluck('id'))->delete();
            $hotelsInUse = ItineraryDay::whereIn('package_id', $kept->pluck('id'))->whereNotNull('hotel_id')->pluck('hotel_id');
            $hotels = Hotel::where('pack', $key)->whereNotIn('id', $hotelsInUse)->delete();
            $destinations = Destination::where('pack', $key)->whereDoesntHave('packages')->delete();
            Category::where('pack', $key)->whereDoesntHave('packages')->delete();
            RoomType::where('pack', $key)->whereNotIn('id', Booking::whereNotNull('room_type_id')->pluck('room_type_id'))->delete();

            return ['removed' => $removable->count() + $hotels + $destinations, 'kept' => $kept->count()];
        });
    }

    /** Pack prices are in USD. */
    private function usdToBase(): float
    {
        $manager = currencies();
        $usd = $manager->find('USD');

        return $manager->base()->code === 'USD' || ! $usd || $manager->rate($usd) <= 0 ? 1.0 : 1 / $manager->rate($usd);
    }
}
