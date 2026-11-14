<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['ADMIN', 'সিস্টেম অ্যাডমিনিস্ট্রেটর'],
            ['AREA_MANAGER', 'সেবা এলাকার ব্যবস্থাপক'],
            ['STAFF', 'মাঠ পর্যায়ের স্টাফ'],
            ['DONOR', 'খাদ্য দাতা'],
        ] as [$name, $desc]) {
            Role::firstOrCreate(['role_name' => $name], ['description' => $desc]);
        }
    }
}
