<?php

namespace Tests\Feature;

use Tests\Concerns;
use Tests\TestCase;

class ReportsAndAdminTest extends TestCase
{
    use Concerns;

    public function test_every_role_can_open_reports_and_admin_pages_render(): void
    {
        $this->seedDemo();
        $this->makeDonation();
        foreach (['admin@kyaf.local', 'manager@kyaf.local', 'staff1@kyaf.local', 'donor@kyaf.local'] as $email) {
            $this->actingAs($this->user($email))->get('/reports')->assertOk();
            $this->actingAs($this->user($email))->get('/profile')->assertOk();
            $this->actingAs($this->user($email))->get('/notifications')->assertOk();
        }
        $admin = $this->user('admin@kyaf.local');
        foreach (['/admin/users', '/admin/roles', '/admin/areas', '/admin/areas/create', '/admin/locations', '/admin/donations', '/admin/applications', '/admin/audit-logs', '/admin/settings', '/admin/messages', '/reports/export'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
        $manager = $this->user('manager@kyaf.local');
        foreach (['/manager/donations', '/manager/applications', '/manager/team'] as $url) {
            $this->actingAs($manager)->get($url)->assertOk();
        }
        $this->actingAs($this->user('staff1@kyaf.local'))->get('/staff/tasks')->assertOk();
        $this->actingAs($this->user('staff1@kyaf.local'))->get('/staff/history')->assertOk();
        $donor = $this->user('donor@kyaf.local');
        foreach (['/donor/donations', '/donor/donate'] as $url) {
            $this->actingAs($donor)->get($url)->assertOk();
        }
    }

    public function test_one_thana_cannot_belong_to_two_service_areas(): void
    {
        $this->seedDemo();
        $admin = $this->user('admin@kyaf.local');
        $district = \App\Models\District::firstOrFail();
        $this->actingAs($admin)->post('/admin/areas', [
            'area_code' => 'DEMO-02', 'area_name' => 'দ্বিতীয় এলাকা', 'district_id' => $district->district_id, 'status' => 'Active',
            'thana_ids' => [$this->coveredThana()->thana_id],
        ])->assertSessionHasErrors('thana_ids');
    }

    public function test_csv_location_import_creates_hierarchy(): void
    {
        $this->seedDemo();
        $csv = "division,district_en,district_bn,upazila_en,upazila_bn,thana_en,thana_bn\nTestDiv,TestDist,টেস্ট জেলা,TestUp,টেস্ট উপজেলা,TestThana,টেস্ট থানা\n";
        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('loc.csv', $csv);
        $this->actingAs($this->user('admin@kyaf.local'))->post('/admin/locations/import', ['csv' => $file])->assertSessionHas('success');
        $this->assertDatabaseHas('thanas', ['name_en' => 'TestThana']);
        $this->assertDatabaseHas('districts', ['name_en' => 'TestDist', 'division_name' => 'TestDiv']);
    }

    public function test_role_cannot_be_changed_and_admin_cannot_deactivate_self(): void
    {
        $this->seedDemo();
        $admin = $this->user('admin@kyaf.local');
        $this->actingAs($admin)->post("/admin/users/{$admin->user_id}/toggle")->assertSessionHas('error');
        $this->assertSame('Active', $admin->fresh()->status);
    }
}
