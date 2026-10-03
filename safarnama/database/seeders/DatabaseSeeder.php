<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\ImageImporter;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@safarnama.test'],
            ['name' => 'Site Admin', 'password' => bcrypt('password')]
        );

        $this->call([
            SettingSeeder::class,
            TravelSeeder::class,
        ]);

        // Register remote images so they show up in Admin → Media library.
        // Downloading happens in `php artisan images:import` / `npm install`.
        app(ImageImporter::class)->scan();
    }
}
