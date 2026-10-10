<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItineraryDay extends Model
{
    protected $fillable = ['package_id', 'hotel_id', 'day_number', 'title', 'description', 'meals'];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }
}
