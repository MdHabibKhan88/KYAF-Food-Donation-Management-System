<?php

namespace Tests;

use App\Models\Donation;
use App\Models\Donor;
use App\Models\Staff;
use App\Models\Thana;
use App\Models\UserAccount;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

/** Shared fixtures: roles, admin and the demo area (manager, two staff, donor, covered thanas 1-2, uncovered thana 3). */
trait Concerns
{
    use RefreshDatabase;

    protected function seedDemo(): void
    {
        $this->seed([RoleSeeder::class, AdminSeeder::class, DemoDataSeeder::class]);
    }

    protected function user(string $email): UserAccount
    {
        return UserAccount::where('email', $email)->firstOrFail();
    }

    protected function donor(): Donor
    {
        return $this->user('donor@kyaf.local')->donor;
    }

    protected function staff(int $n = 1): Staff
    {
        return $this->user("staff{$n}@kyaf.local")->staff;
    }

    protected function coveredThana(): Thana
    {
        return Thana::where('name_en', 'Demo Thana 1')->firstOrFail();
    }

    protected function donationData(array $over = []): array
    {
        return array_merge([
            'food_name' => 'টেস্ট খিচুড়ি', 'food_category' => 'Cooked', 'quantity_meals' => 40,
            'expiry_date' => now()->timezone('Asia/Dhaka')->addDays(2)->toDateString(), 'expiry_time' => '18:00',
            'description' => null, 'pickup_thana_id' => $this->coveredThana()->thana_id,
            'pickup_address' => 'টেস্ট রোড, বাড়ি ১', 'contact_phone' => '+8801711111111',
        ], $over);
    }

    protected function makeDonation(array $over = []): Donation
    {
        return app(\App\Services\DonationService::class)->submit($this->donor(), $this->donationData($over), null, true);
    }
}
