<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Package extends Model
{
    public const DIFFICULTIES = ['Easy', 'Moderate', 'Active', 'Challenging'];

    protected $fillable = [
        'category_id', 'country', 'pack', 'title', 'slug', 'summary', 'overview', 'duration_days', 'price', 'original_price',
        'child_price', 'group_tiers', 'room_options', 'default_room_type_id', 'extras', 'option_groups', 'date_mode',
        'group_size', 'difficulty', 'best_season', 'start_city', 'hero_image', 'gallery', 'highlights',
        'inclusions', 'exclusions', 'is_featured', 'is_published', 'rating', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'gallery' => 'array',
            'group_tiers' => 'array',
            'room_options' => 'array',
            'extras' => 'array',
            'option_groups' => 'array',
            'child_price' => 'decimal:2',
            'highlights' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'rating' => 'decimal:1',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class);
    }

    public function vibes(): BelongsToMany
    {
        return $this->belongsToMany(Vibe::class)->orderBy('sort_order');
    }

    public function getCountryNameAttribute(): ?string
    {
        return $this->country ? (config('countries')[$this->country] ?? $this->country) : null;
    }

    public function days(): HasMany
    {
        return $this->hasMany(ItineraryDay::class)->orderBy('day_number');
    }

    public function departures(): HasMany
    {
        return $this->hasMany(Departure::class)->orderBy('starts_on');
    }

    /** Whether guests choose from fixed departures ("batches") instead of, or as well as, their own date. */
    public function usesDepartures(): bool
    {
        return in_array($this->date_mode, ['departures', 'both'], true);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getHeroImageUrlAttribute(): ?string
    {
        return media_url($this->hero_image);
    }

    public function getGalleryUrlsAttribute(): array
    {
        return array_values(array_filter(array_map('media_url', $this->gallery ?? [])));
    }

    public function getNightsAttribute(): int
    {
        return max($this->duration_days - 1, 0);
    }

    /** Distinct hotels used across the itinerary, in order of first appearance. */
    public function stays()
    {
        return $this->days->pluck('hotel')->filter()->unique('id')->values();
    }
}
