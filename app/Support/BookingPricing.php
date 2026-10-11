<?php

namespace App\Support;

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

        $rooms = RoomType::active()->get()
            ->filter(fn (RoomType $type) => $overrides === [] || ($overrides[$type->id]['enabled'] ?? false))
            ->map(fn (RoomType $type) => [
                'id' => $type->id,
                'name' => $type->name,
                'description' => $type->description,
                'adjustment' => round((float) ($overrides[$type->id]['adjustment'] ?? $type->default_adjustment), 2),
                'default' => false,
            ])->values();

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
        ];
    }

    /**
     * @param  array<int, string>  $extraKeys
     * @return array{adult_price: float, child_price: float, group_price: bool, room: ?array, extras: array, lines: array, total: float}
     *
     * @throws ValidationException when the room or an extra is not offered for this journey
     */
    public function quote(Package $package, int $adults, int $children, ?int $roomTypeId, array $extraKeys = []): array
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
            'lines' => $lines,
            'total' => round(max(array_sum(array_column($lines, 'amount')), 0), 2),
        ];
    }
}
