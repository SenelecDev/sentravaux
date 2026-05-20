<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            RolesAndPermissionsSeeder::class,
            ReferenceDataSeeder::class,
            AdminUserSeeder::class,
        ];

        // Faker / factories : développement uniquement (composer --no-dev en prod)
        if (app()->environment('local')) {
            $seeders[] = SimulateUsersSeeder::class;
        }

        $this->call($seeders);
    }
}
