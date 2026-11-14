<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RoleSeeder::class, AdminSeeder::class]);

        // Demo data (clearly fake names, local testing only). Never seeded outside APP_ENV=local.
        if (app()->environment('local')) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
