<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'tagline', 'description', 'image', 'sort_order'];

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getImageUrlAttribute(): ?string
    {
        return media_url($this->image);
    }
}
