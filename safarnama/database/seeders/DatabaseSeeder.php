<?php

namespace Database\Seeders;

use App\Models\User;
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
    }
}
