<?php

namespace App\Models;

use Database\Factories\DepartureFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * A fixed start date ("batch") of a journey with limited seats.
 */
class Departure extends Model
{
    /** @use HasFactory<DepartureFactory> */
    use HasFactory;

    protected $fillable = ['package_id', 'starts_on', 'seats', 'price_adjustment', 'label', 'is_closed'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'price_adjustment' => 'float', 'is_closed' => 'boolean', 'seats' => 'integer'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /** Departures guests can still choose: open and starting after today. */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('is_closed', false)->whereDate('starts_on', '>', today())->orderBy('starts_on');
    }

    /** Travellers on bookings that have not been cancelled. */
    public function seatsTaken(): int
    {
        return (int) $this->bookings()->where('status', '!=', 'cancelled')->selectRaw('coalesce(sum(adults + children), 0) as n')->value('n');
    }

    /** Seats still free, or null when the departure has no limit. */
    public function seatsLeft(): ?int
    {
        return $this->seats === null ? null : max(0, $this->seats - $this->seatsTaken());
    }

    public function endsOn(): Carbon
    {
        return $this->starts_on->copy()->addDays(max(1, (int) $this->package->duration_days) - 1);
    }

    /** e.g. "12 – 20 Nov 2026". */
    public function dateRange(): string
    {
        $start = $this->starts_on;
        $end = $this->endsOn();

        return match (true) {
            $start->isSameMonth($end) => $start->format('j').' – '.$end->format('j M Y'),
            $start->isSameYear($end) => $start->format('j M').' – '.$end->format('j M Y'),
            default => $start->format('j M Y').' – '.$end->format('j M Y'),
        };
    }
}
