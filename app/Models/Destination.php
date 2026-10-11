<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    /** Suggestions for Pakistan; regions are free text so any country works. */
    public const REGIONS = ['Punjab', 'Sindh', 'Khyber Pakhtunkhwa', 'Gilgit-Baltistan', 'Balochistan', 'Azad Kashmir', 'Islamabad Capital'];

    protected $fillable = ['name', 'slug', 'country', 'pack', 'region', 'tagline', 'description', 'image', 'highlights', 'best_season', 'is_featured', 'sort_order'];

    protected function casts(): array
    {
        return ['highlights' => 'array', 'is_featured' => 'boolean'];
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class);
    }

    public function hotels(): HasMany
    {
        return $this->hasMany(Hotel::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getCountryNameAttribute(): string
    {
        return config('countries')[$this->country] ?? $this->country;
    }

    public function getImageUrlAttribute(): ?string
    {
        return media_url($this->image);
    }
}
