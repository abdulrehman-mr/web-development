<?php

namespace Database\Seeders;

use App\Models\Vibe;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VibeSeeder extends Seeder
{
    /** @var array<int, array{0: string, 1: string, 2: string}> name, description, icon */
    public const DEFAULTS = [
        ['Heritage & history', 'Forts, palaces, ancient cities and living traditions.', 'landmark'],
        ['Romantic', 'Honeymoons, anniversaries and trips for two.', 'heart'],
        ['Religious & spiritual', 'Pilgrimages, sacred sites, shrines and retreats.', 'moon'],
        ['Countryside', 'Villages, farms, vineyards and slow travel.', 'tree'],
        ['Nature & wildlife', 'Mountains, lakes, national parks and safaris.', 'mountain'],
        ['Adventure', 'Treks, road trips and the great outdoors.', 'compass'],
        ['Beach & islands', 'Turquoise water, overwater villas and island hopping.', 'wave'],
        ['City breaks', 'Skylines, museums, nightlife and neighbourhoods.', 'building'],
        ['Food & culture', 'Street food, cooking classes and local life.', 'utensils'],
        ['Festivals & events', 'Celebrations, concerts and seasonal spectacles.', 'music'],
        ['Tech-savvy', 'Futuristic cities, gadgets, robots and innovation.', 'cpu'],
        ['Family-friendly', 'Easy pace, theme parks and fun for all ages.', 'users'],
        ['Luxury', 'Five-star stays, private guides and the finest experiences.', 'gem'],
        ['Shopping', 'Bazaars, malls, markets and designer streets.', 'bag'],
        ['Wellness', 'Spas, yoga, hot springs and time to recharge.', 'leaf'],
    ];

    public function run(): void
    {
        foreach (self::DEFAULTS as $i => [$name, $description, $icon]) {
            Vibe::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'description' => $description, 'icon' => $icon, 'sort_order' => $i]);
        }
    }
}
