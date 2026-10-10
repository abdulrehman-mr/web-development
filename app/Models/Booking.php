<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];

    protected $attributes = ['status' => 'pending', 'children' => 0];

    protected $fillable = [
        'reference', 'package_id', 'full_name', 'email', 'phone', 'country', 'adults', 'children',
        'start_date', 'room_preference', 'special_requests', 'total_estimate', 'status', 'admin_notes',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'total_estimate' => 'decimal:2'];
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
}
