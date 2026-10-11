<?php

namespace Database\Seeders;

use App\Models\RoomType;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
{
    /** Starter room options; prices are per adult for the whole trip, in the base currency. */
    public function run(): void
    {
        if (RoomType::exists()) {
            return;
        }

        foreach ([
            ['Twin sharing', 'Two guests share a room with two separate beds. Our standard price.', 0],
            ['Double room', 'Two guests share one large bed. Ideal for couples.', 0],
            ['Single room', 'A private room just for you. Adds a single-room supplement.', 450],
            ['Triple sharing', 'Three guests share one room. Saves a little per person.', -90],
            ['Family room', 'Parents and children together in a larger room or connecting rooms.', -60],
        ] as $i => [$name, $description, $adjustment]) {
            RoomType::create(['name' => $name, 'description' => $description, 'default_adjustment' => $adjustment, 'sort_order' => $i]);
        }
    }
}
