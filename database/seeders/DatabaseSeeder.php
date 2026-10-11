<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\User;
use App\Support\ImageImporter;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Safe to run on every deploy: the admin account and sample content are only
     * created once, so later runs never reset the password or overwrite edits.
     */
    public function run(): void
    {
        if (! User::exists()) {
            User::create(['name' => 'Site Admin', 'email' => 'admin@safarnama.test', 'password' => bcrypt('password')]);
        }

        if (Package::exists()) {
            return;
        }

        $this->call([
            SettingSeeder::class,
            TravelSeeder::class,
        ]);

        // Register remote images so they show up in Admin → Media library.
        // Downloading happens in `php artisan images:import` / `npm install`.
        app(ImageImporter::class)->scan();
    }
}
