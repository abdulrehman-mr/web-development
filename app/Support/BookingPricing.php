<?php

namespace App\Support;

use App\Models\Hotel;
use App\Models\Package;
use App\Models\RoomType;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Prices a journey for a party of travellers. All amounts are in the base currency.
 *
 * total = adults × adult price (group price when the party is big enough)
 *       + children × child price
 *       + adults × room supplement/discount
 *       + extras (per person or per booking)
 */
class BookingPricing
{
    /** Children (2–11) pay this share of the adult price when a journey has no child price. */
    public const CHILD_RATE = 0.7;

    /**
     * @return Collection<int, array{id: int, name: string, description: ?string, adjustment: float, default: bool}>
     */
    public function rooms(Package $package): Collection
    {
        $overrides = $package->room_options ?? [];

        // Without explicit choices a journey offers the standard room types (not ones added by content packs).
        $rooms = RoomType::active()->get()
            ->filter(fn (RoomType $type) => $overrides === [] ? $type->pack === null : ($overrides[$type->id]['enabled'] ?? false))
            ->map(fn (RoomType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'adjustment' => round((float) ($overrides[$type->id]['adjustment'] ?? $type->default_adjustment), 2),
                'default' => false,
            ])->values();

        if ($overrides !== []) {
            $order = array_flip(array_map('intval', array_keys($overrides)));
            $rooms = $rooms->sortBy(fn (array $room) => $order[$room['id']] ?? PHP_INT_MAX)->values();
        }

        $defaultId = $rooms->firstWhere('id', $package->default_room_type_id)['id'] ?? $rooms->first()['id'] ?? null;

        return $rooms->map(fn (array $room) => [...$room, 'default' => $room['id'] === $defaultId]);
    }

    /**
     * @return Collection<int, array{key: string, name: string, description: ?string, price: float, per: string}>
     */
    public function extras(Package $package): Collection
    {
        return collect($package->extras ?? [])->values()->map(fn (array $extra, int $i) => [
            'key' => (string) $i,
            'name' => (string) $extra['name'],
            'description' => $extra['description'] ?? null,
            'price' => round((float) $extra['price'], 2),
            'per' => ($extra['per'] ?? 'person') === 'booking' ? 'booking' : 'person',
        ]);
    }

    /**
     * Per-journey choices such as "Hotel in Makkah" or "Package class". The first option is the default.
     *
     * @return Collection<int, array{key: string, name: string, description: ?string, options: array<int, array<string, mixed>>}>
     */
    public function groups(Package $package): Collection
    {
        $groups = collect($package->option_groups ?? [])->values();
        $hotelIds = $groups->flatMap(fn ($g) => array_column($g['options'] ?? [], 'hotel_id'))->filter()->unique();
        $hotels = Hotel::whereIn('id', $hotelIds)->get()->keyBy('id');

        return $groups->map(fn (array $group, int $i) => [
            'key' => (string) $i,
            'name' => (string) $group['name'],
            'description' => $group['description'] ?? null,
            'options' => collect($group['options'] ?? [])->values()->map(function (array $option, int $j) use ($hotels) {
                $hotel = isset($option['hotel_id']) ? $hotels->get($option['hotel_id']) : null;

                return [
                    'key' => (string) $j,
                    'label' => (string) (($option['label'] ?? '') ?: $hotel?->name ?? 'Option '.($j + 1)),
                    'description' => ($option['description'] ?? null) ?: $hotel?->description,
                    'price' => round((float) ($option['price'] ?? 0), 2),
                    'per' => in_array($option['per'] ?? 'adult', ['adult', 'person', 'booking'], true) ? ($option['per'] ?? 'adult') : 'adult',
                    'hotel' => $hotel ? ['name' => $hotel->name, 'address' => $hotel->address, 'stars' => $hotel->stars, 'image' => $hotel->image_url] : null,
                ];
            })->all(),
        ])->filter(fn (array $group) => $group['options'] !== [])->values();
    }

    /**
     * @return Collection<int, array{min: int, price: float}>
     */
    public function tiers(Package $package): Collection
    {
        return collect($package->group_tiers ?? [])
            ->map(fn (array $tier) => ['min' => (int) $tier['min'], 'price' => round((float) $tier['price'], 2)])
            ->filter(fn (array $tier) => $tier['min'] >= 2)
            ->sortBy('min')->values();
    }

    public function adultPrice(Package $package, int $travellers): float
    {
        $tier = $this->tiers($package)->filter(fn (array $tier) => $travellers >= $tier['min'])->last();

        return $tier['price'] ?? (float) $package->price;
    }

    public function childPrice(Package $package, int $travellers): float
    {
        return $package->child_price !== null
            ? (float) $package->child_price
            : round($this->adultPrice($package, $travellers) * self::CHILD_RATE, 2);
    }

    /**
     * Everything the booking page needs to price a party in the browser.
     *
     * @return array<string, mixed>
     */
    public function config(Package $package): array
    {
        return [
            'adultPrice' => (float) $package->price,
            'childPrice' => $package->child_price !== null ? (float) $package->child_price : null,
            'childRate' => self::CHILD_RATE,
            'tiers' => $this->tiers($package)->all(),
            'rooms' => $this->rooms($package)->all(),
            'extras' => $this->extras($package)->all(),
            'groups' => $this->groups($package)->all(),
        ];
    }

    /**
     * @param  array<int, string>  $extraKeys
     * @param  array<string, string>  $optionKeys  group key => option key (missing groups use their first option)
     * @return array{adult_price: float, child_price: float, group_price: bool, room: ?array, extras: array, options: array, lines: array, total: float}
     *
     * @throws ValidationException when the room, an extra or an option is not offered for this journey
     */
    public function quote(Package $package, int $adults, int $children, ?int $roomTypeId, array $extraKeys = [], array $optionKeys = []): array
    {
        $travellers = $adults + $children;
        $adultPrice = $this->adultPrice($package, $travellers);
        $childPrice = $this->childPrice($package, $travellers);

        $rooms = $this->rooms($package);
        $room = $roomTypeId ? $rooms->firstWhere('id', $roomTypeId) : $rooms->firstWhere('default', true);
        if ($roomTypeId && ! $room) {
            throw ValidationException::withMessages(['room_type_id' => 'Please choose one of the room options offered for this journey.']);
        }

        $available = $this->extras($package)->keyBy('key');
        $unknown = array_diff($extraKeys, $available->keys()->all());
        if ($unknown !== []) {
            throw ValidationException::withMessages(['extras' => 'One of the selected extras is not available.']);
        }
        $extras = $available->only($extraKeys)->values();

        $lines = [['label' => $adults.' × adult'.($adultPrice < (float) $package->price ? ' (group price)' : ''), 'amount' => $adults * $adultPrice]];
        if ($children > 0) {
            $lines[] = ['label' => $children.' × child', 'amount' => $children * $childPrice];
        }
        if ($room && $room['adjustment'] != 0) {
            $lines[] = ['label' => $room['name'].' × '.$adults.' adult'.($adults > 1 ? 's' : ''), 'amount' => $adults * $room['adjustment']];
        }
        $chosenOptions = [];
        foreach ($this->groups($package) as $group) {
            $option = collect($group['options'])->firstWhere('key', (string) ($optionKeys[$group['key']] ?? '0'));
            if (! $option) {
                throw ValidationException::withMessages(['options' => "Please choose one of the options for “{$group['name']}”."]);
            }
            $chosenOptions[] = ['group' => $group['name'], 'label' => $option['label'], 'price' => $option['price'], 'per' => $option['per']];
            if ($option['price'] != 0) {
                $quantity = match ($option['per']) {
                    'person' => $travellers,
                    'booking' => 1,
                    default => $adults,
                };
                $lines[] = ['label' => $group['name'].': '.$option['label'].($quantity > 1 ? ' × '.$quantity : ''), 'amount' => $quantity * $option['price']];
            }
        }
        foreach ($extras as $extra) {
            $quantity = $extra['per'] === 'person' ? $travellers : 1;
            $lines[] = ['label' => $extra['name'].($quantity > 1 ? ' × '.$quantity : ''), 'amount' => $quantity * $extra['price']];
        }

        return [
            'adult_price' => $adultPrice,
            'child_price' => $childPrice,
            'group_price' => $adultPrice < (float) $package->price,
            'room' => $room,
            'extras' => $extras->all(),
            'options' => $chosenOptions,
            'lines' => $lines,
            'total' => round(max(array_sum(array_column($lines, 'amount')), 0), 2),
        ];
    }
}
