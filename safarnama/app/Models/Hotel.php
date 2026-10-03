<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Hotel extends Model
{
    public const TYPES = ['Heritage hotel', 'Luxury hotel', 'Boutique hotel', 'Mountain lodge', 'Desert camp', 'Beach resort', 'Guesthouse'];

    protected $fillable = ['destination_id', 'name', 'slug', 'type', 'stars', 'description', 'image', 'amenities', 'address'];

    protected function casts(): array
    {
        return ['amenities' => 'array'];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return media_url($this->image);
    }
}
