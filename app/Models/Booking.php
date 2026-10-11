<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Booking extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];

    protected $attributes = ['status' => 'pending', 'children' => 0, 'payment_status' => 'unpaid', 'amount_paid' => 0, 'exchange_rate' => 1];

    protected $fillable = [
        'reference', 'package_id', 'departure_id', 'full_name', 'email', 'phone', 'country', 'adults', 'children',
        'start_date', 'room_preference', 'special_requests', 'total_estimate', 'status', 'admin_notes',
        'currency', 'exchange_rate', 'amount_paid', 'payment_status', 'room_type_id', 'extras', 'options', 'price_breakdown',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'total_estimate' => 'decimal:2', 'exchange_rate' => 'float', 'amount_paid' => 'decimal:2', 'extras' => 'array', 'options' => 'array', 'price_breakdown' => 'array'];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            $booking->reference ??= static::newReference();
        });
    }

    public static function newReference(): string
    {
        do {
            $ref = 'SFR-'.strtoupper(Str::random(6));
        } while (static::where('reference', $ref)->exists());

        return $ref;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(Departure::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function getTravelersAttribute(): int
    {
        return $this->adults + $this->children;
    }

    /** Remaining amount in the base currency. */
    public function balanceDue(): float
    {
        return max(round((float) $this->total_estimate - (float) $this->amount_paid, 2), 0);
    }

    /** Deposit in the base currency (capped at what is still owed). */
    public function depositAmount(): float
    {
        $percent = min(max((float) setting('deposit_percent', 25), 1), 100);

        return min(round((float) $this->total_estimate * $percent / 100, 2), $this->balanceDue());
    }

    public function refreshPaymentStatus(): void
    {
        $paid = (float) $this->payments()->where('status', Payment::PAID)->sum('base_amount');

        $this->update([
            'amount_paid' => $paid,
            'payment_status' => match (true) {
                $paid <= 0 => 'unpaid',
                $paid + 0.01 >= (float) $this->total_estimate => 'paid',
                default => 'deposit_paid',
            },
        ]);
    }
}
