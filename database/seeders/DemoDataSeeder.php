<?php

namespace Database\Seeders;

use App\Models\AreaManager;
use App\Models\District;
use App\Models\Donor;
use App\Models\Role;
use App\Models\ServiceArea;
use App\Models\Thana;
use App\Models\Upazila;
use App\Models\UserAccount;
use App\Services\DonationService;
use App\Services\PasswordPolicy;
use App\Services\TeamService;
use App\Support\Phone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Local-testing data only. Location names are deliberately fake ("Demo ...") because the real Bangladesh
 * administrative data must be imported by the Admin from a genuine source (Admin > Locations > CSV import).
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (ServiceArea::where('area_code', 'DEMO-01')->exists()) {
            return;
        }
        DB::transaction(function () {
            $district = District::create(['name_en' => 'Demo District', 'name_bn' => 'ডেমো জেলা', 'division_name' => 'ডেমো বিভাগ']);
            $upA = Upazila::create(['district_id' => $district->district_id, 'name_en' => 'Demo Upazila A', 'name_bn' => 'ডেমো উপজেলা ক']);
            $upB = Upazila::create(['district_id' => $district->district_id, 'name_en' => 'Demo Upazila B', 'name_bn' => 'ডেমো উপজেলা খ']);
            $t1 = Thana::create(['upazila_id' => $upA->upazila_id, 'name_en' => 'Demo Thana 1', 'name_bn' => 'ডেমো থানা ১']);
            $t2 = Thana::create(['upazila_id' => $upA->upazila_id, 'name_en' => 'Demo Thana 2', 'name_bn' => 'ডেমো থানা ২']);
            Thana::create(['upazila_id' => $upB->upazila_id, 'name_en' => 'Demo Thana 3 (uncovered)', 'name_bn' => 'ডেমো থানা ৩ (সেবা এলাকার বাইরে)']);

            $mUser = $this->user('AREA_MANAGER', 'ডেমো এরিয়া ম্যানেজার', 'manager@kyaf.local', '01700000002', 'Manager@12345');
            $manager = AreaManager::create(['user_id' => $mUser->user_id, 'nid_number' => '1234567890123', 'appointed_at' => now(), 'status' => 'Active']);

            $area = ServiceArea::create(['area_code' => 'DEMO-01', 'area_name' => 'ডেমো সেবা এলাকা ১', 'district_id' => $district->district_id, 'manager_id' => $manager->manager_id, 'status' => 'Active']);
            Thana::whereIn('thana_id', [$t1->thana_id, $t2->thana_id])->update(['area_id' => $area->area_id]);

            $team = app(TeamService::class);
            foreach ([['ডেমো স্টাফ ১', 'staff1@kyaf.local', '01700000003'], ['ডেমো স্টাফ ২', 'staff2@kyaf.local', '01700000004']] as [$n, $e, $p]) {
                $u = $this->user('STAFF', $n, $e, $p, 'Staff@12345');
                $team->addStaff($manager, $u->user_id, null);
            }

            $dUser = $this->user('DONOR', 'ডেমো দাতা', 'donor@kyaf.local', '01700000005', 'Donor@12345');
            $donor = Donor::create([
                'donor_id' => Donor::nextId(), 'user_id' => $dUser->user_id, 'donor_type' => 'Individual', 'thana_id' => $t1->thana_id,
                'address_line' => 'ডেমো ঠিকানা, ডেমো থানা ১', 'registered_at' => now(),
            ]);

            app(DonationService::class)->submit($donor, [
                'food_name' => 'ডেমো বিরিয়ানি', 'food_category' => 'Cooked', 'quantity_meals' => 50,
                'expiry_date' => now()->timezone('Asia/Dhaka')->addDay()->toDateString(), 'expiry_time' => '20:00',
                'description' => 'ডেমো ডেটা — পরীক্ষার জন্য', 'pickup_thana_id' => $t1->thana_id,
                'pickup_address' => 'ডেমো রোড, ডেমো থানা ১', 'contact_phone' => $dUser->phone,
            ], null, true);
        });
    }

    private function user(string $role, string $name, string $email, string $phone, string $password): UserAccount
    {
        $u = UserAccount::create([
            'role_id' => Role::where('role_name', $role)->value('role_id'), 'full_name' => $name, 'email' => $email,
            'phone' => Phone::normalize($phone), 'password_hash' => 'pending', 'status' => 'Active',
        ]);
        PasswordPolicy::set($u, $password);

        return $u;
    }
}
