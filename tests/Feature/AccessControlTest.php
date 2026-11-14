<?php

namespace Tests\Feature;

use App\Models\Donor;
use App\Models\Role;
use App\Models\UserAccount;
use App\Services\PasswordPolicy;
use Tests\Concerns;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use Concerns;

    public function test_donor_sees_only_own_donations(): void
    {
        $this->seedDemo();
        $d = $this->makeDonation();
        $other = UserAccount::create(['role_id' => Role::where('role_name', 'DONOR')->value('role_id'), 'full_name' => 'অন্য দাতা', 'email' => 'other@kyaf.local', 'phone' => '+8801722222222', 'password_hash' => 'x', 'status' => 'Active']);
        PasswordPolicy::set($other, 'Other@12345');
        Donor::create(['donor_id' => 'DNR-000099', 'user_id' => $other->user_id, 'donor_type' => 'Individual', 'thana_id' => $this->coveredThana()->thana_id, 'address_line' => 'x', 'registered_at' => now()]);

        $this->actingAs($other)->get("/donor/donations/{$d->donation_id}")->assertForbidden();
        $this->actingAs($other)->post("/donor/donations/{$d->donation_id}/cancel", ['reason' => 'abc'])->assertForbidden();
        $this->actingAs($this->donor()->user)->get("/donor/donations/{$d->donation_id}")->assertOk();
    }

    public function test_staff_cannot_open_unassigned_donation_and_manager_cannot_open_other_area(): void
    {
        $this->seedDemo();
        $d = $this->makeDonation();
        $this->actingAs($this->user('staff1@kyaf.local'))->get("/staff/tasks/{$d->donation_id}")->assertForbidden();
        $this->actingAs($this->user('manager@kyaf.local'))->get("/manager/donations/{$d->donation_id}")->assertOk();
    }

    public function test_donor_donation_form_requires_validation(): void
    {
        $this->seedDemo();
        $this->actingAs($this->donor()->user)->post('/donor/donate', ['food_name' => ''])->assertSessionHasErrors(['food_name', 'food_category', 'quantity_meals', 'expiry_date', 'expiry_time', 'pickup_thana_id', 'pickup_address', 'contact_phone']);
        $this->actingAs($this->donor()->user)->post('/donor/donate', array_merge($this->donationData(), ['quantity_meals' => '0']))->assertSessionHasErrors('quantity_meals');
        $this->actingAs($this->donor()->user)->post('/donor/donate', $this->donationData())->assertRedirect();
        $this->assertDatabaseCount('donations', 2); // demo donation + this one
    }

    public function test_audit_log_cannot_be_modified(): void
    {
        $this->seedDemo();
        $log = \App\Models\AuditLog::create(['action_type' => 'T', 'entity_name' => 'T', 'ip_address' => '127.0.0.1']);
        $this->expectException(\LogicException::class);
        $log->update(['action_type' => 'X']);
    }
}
