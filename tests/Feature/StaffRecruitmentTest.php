<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\StaffApplication;
use App\Models\UserAccount;
use App\Services\ApplicationService;
use App\Services\PasswordPolicy;
use App\Services\TeamService;
use DomainException;
use Tests\Concerns;
use Tests\TestCase;

class StaffRecruitmentTest extends TestCase
{
    use Concerns;

    private function applyPayload(array $over = []): array
    {
        $area = \App\Models\ServiceArea::where('area_code', 'DEMO-01')->first();

        return array_merge([
            'applicant_name' => 'আবেদনকারী', 'email' => 'applicant@example.com', 'phone' => '01733333333', 'nid_number' => '1234567890',
            'date_of_birth' => now()->subYears(25)->toDateString(), 'education_level' => 'স্নাতক (সম্মান/পাস)',
            'thana_id' => $this->coveredThana()->thana_id, 'area_id' => $area->area_id,
        ], $over);
    }

    private function fillTeam(): void
    {
        $manager = $this->user('manager@kyaf.local')->manager;
        $team = app(TeamService::class);
        for ($i = 3; $i <= 5; $i++) {
            $u = UserAccount::create(['role_id' => Role::where('role_name', 'STAFF')->value('role_id'), 'full_name' => "স্টাফ {$i}", 'email' => "s{$i}@kyaf.local", 'phone' => '+88017000001'.$i.'0', 'password_hash' => 'x', 'status' => 'Active']);
            PasswordPolicy::set($u, 'Staff@12345');
            $team->addStaff($manager, $u->user_id, null);
        }
    }

    public function test_application_validation_age_nid_and_duplicates(): void
    {
        $this->seedDemo();
        $this->post('/become-staff', $this->applyPayload(['date_of_birth' => now()->subYears(17)->toDateString()]))->assertSessionHasErrors('date_of_birth');
        $this->post('/become-staff', $this->applyPayload(['nid_number' => '123']))->assertSessionHasErrors('nid_number');
        $this->post('/become-staff', $this->applyPayload(['phone' => '123']))->assertSessionHasErrors('phone');
        $this->post('/become-staff', $this->applyPayload())->assertRedirect('/become-staff');
        $this->assertDatabaseHas('staff_applications', ['email' => 'applicant@example.com', 'status' => 'Submitted', 'phone' => '+8801733333333']);
        // BR-21: one open application per NID
        $this->post('/become-staff', $this->applyPayload(['email' => 'second@example.com', 'phone' => '01744444444']))->assertSessionHasErrors('nid_number');
    }

    public function test_sixth_active_staff_is_never_allowed(): void
    {
        $this->seedDemo();
        $this->fillTeam(); // 2 demo + 3 = 5
        $manager = $this->user('manager@kyaf.local')->manager;
        $this->assertSame(5, $manager->activeStaffCount());
        $u = UserAccount::create(['role_id' => Role::where('role_name', 'STAFF')->value('role_id'), 'full_name' => 'ষষ্ঠ', 'email' => 's6@kyaf.local', 'phone' => '+8801700000999', 'password_hash' => 'x', 'status' => 'Active']);
        $this->expectException(DomainException::class);
        app(TeamService::class)->addStaff($manager, $u->user_id, null);
    }

    public function test_approval_needs_vacancy_then_invitation_creates_staff_once(): void
    {
        $this->seedDemo();
        $this->post('/become-staff', $this->applyPayload());
        $app = StaffApplication::firstOrFail();
        $managerUser = $this->user('manager@kyaf.local');

        $this->fillTeam();
        try {
            app(ApplicationService::class)->approve($app, $managerUser);
            $this->fail('no vacancy: approval must be refused');
        } catch (DomainException $e) {
            $this->assertSame('Submitted', $app->fresh()->status);
        }

        // free a seat
        $this->user('s3@kyaf.local')->staff->update(['status' => 'Removed']);
        $link = app(ApplicationService::class)->approve($app->fresh(), $managerUser);
        $token = basename($link);
        $this->assertSame('Approved', $app->fresh()->status);

        $this->get("/staff-invitation/{$token}")->assertOk();
        $this->post("/staff-invitation/{$token}", ['password' => 'Newstaff@123', 'password_confirmation' => 'Newstaff@123', 'terms' => '1'])->assertRedirect('/login/staff');
        $this->assertSame('Registered', $app->fresh()->status);
        $this->assertTrue(UserAccount::where('email', 'applicant@example.com')->exists());

        // single use
        $this->post("/staff-invitation/{$token}", ['password' => 'Newstaff@123', 'password_confirmation' => 'Newstaff@123', 'terms' => '1'])->assertRedirect('/login');
    }

    public function test_invitation_expires_after_72_hours(): void
    {
        $this->seedDemo();
        $this->post('/become-staff', $this->applyPayload());
        $token = basename(app(ApplicationService::class)->approve(StaffApplication::firstOrFail(), $this->user('manager@kyaf.local')));
        $this->travel(73)->hours();
        $this->post("/staff-invitation/{$token}", ['password' => 'Newstaff@123', 'password_confirmation' => 'Newstaff@123', 'terms' => '1'])->assertRedirect('/login');
        $this->assertSame('Expired', StaffApplication::firstOrFail()->status);
    }
}
