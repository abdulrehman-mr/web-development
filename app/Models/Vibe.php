<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Vibe extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'icon', 'sort_order'];

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
