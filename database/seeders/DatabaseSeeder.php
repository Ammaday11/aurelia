<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters: settings and the admin account have no dependencies,
     * categories must exist before products reference them.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SettingsSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
