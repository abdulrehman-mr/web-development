<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    protected $fillable = ['package_id', 'name', 'location', 'quote', 'rating', 'is_published', 'sort_order'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
