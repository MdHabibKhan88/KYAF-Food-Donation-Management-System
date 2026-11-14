<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\UserAccount;
use App\Services\PasswordPolicy;
use App\Support\Phone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** Creates the initial Admin. The password comes from .env (KYAF_ADMIN_PASSWORD); nothing is hard-coded for production. */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = Str::lower(env('KYAF_ADMIN_EMAIL', 'admin@kyaf.local'));
        if (UserAccount::where('email', $email)->exists()) {
            return;
        }
        $password = env('KYAF_ADMIN_PASSWORD');
        if (! $password) {
            // Local demo default; any other environment gets a random password printed once.
            $password = app()->environment('local') ? 'Admin@12345' : 'Kx!'.Str::random(10).'9a';
            $this->command?->warn("Initial admin password: {$password}");
        }
        $user = UserAccount::create([
            'role_id' => Role::where('role_name', Role::ADMIN)->value('role_id'),
            'full_name' => 'সিস্টেম অ্যাডমিন', 'email' => $email,
            'phone' => Phone::normalize(env('KYAF_ADMIN_PHONE', '01700000001')),
            'password_hash' => 'pending', 'status' => 'Active',
        ]);
        PasswordPolicy::set($user, $password);
    }
}
