<?php

namespace App\Models;

use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    /** Built-in pages: slug => public route name. */
    public const SYSTEM = [
        'home' => 'home',
        'journeys' => 'packages.index',
        'destinations' => 'destinations.index',
        'stays' => 'stays',
        'about' => 'about',
        'contact' => 'contact',
    ];

    protected $fillable = ['slug', 'title', 'meta_description', 'is_system', 'is_published', 'show_in_menu'];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_published' => 'boolean',
            'show_in_menu' => 'boolean',
        ];
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** A built-in page, created on the fly if it is missing. */
    public static function system(string $slug): self
    {
        return static::firstOrCreate(['slug' => $slug], ['title' => ucfirst($slug), 'is_system' => true]);
    }

    public function url(): string
    {
        return isset(self::SYSTEM[$this->slug]) ? route(self::SYSTEM[$this->slug]) : route('pages.show', $this);
    }
}
