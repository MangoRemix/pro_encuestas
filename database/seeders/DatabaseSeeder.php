<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            ParishSeeder::class,
            SexSeeder::class,
            // AgeRangeSeeder::class,
            PersonSeeder::class,
        ]);
    }
}
