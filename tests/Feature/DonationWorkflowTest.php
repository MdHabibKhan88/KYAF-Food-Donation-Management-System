<?php

namespace Tests\Feature;

use App\Models\Donation;
use App\Services\DonationService;
use DomainException;
use Tests\Concerns;
use Tests\TestCase;

class DonationWorkflowTest extends TestCase
{
    use Concerns;

    public function test_full_lifecycle_submitted_to_completed_with_history_and_notifications(): void
    {
        $this->seedDemo();
        $svc = app(DonationService::class);
        $manager = $this->user('manager@kyaf.local');
        $staffUser = $this->user('staff1@kyaf.local');

        $d = $this->makeDonation();
        $this->assertSame('Submitted', $d->status);
        $this->assertMatchesRegularExpression('/^DON-\d{4}-\d{6}$/', $d->donation_code);

        $svc->assign($d, $this->staff(1)->staff_id, $manager);
        $svc->verify($d->fresh(), $staffUser, 'Verified', 'Good', 38, 'ঠিক আছে');
        $svc->decide($d->fresh(), $manager, 'Approved', null);
        $svc->collect($d->fresh(), $staffUser, 36, null);
        $svc->planDistribution($d->fresh(), $staffUser, [
            'point_name' => 'টেস্ট কেন্দ্র', 'point_address' => 'টেস্ট ঠিকানা', 'thana_id' => $this->coveredThana()->thana_id,
            'scheduled_start' => now()->addHour()->timezone('Asia/Dhaka')->format('Y-m-d\TH:i'),
            'scheduled_end' => now()->addHours(3)->timezone('Asia/Dhaka')->format('Y-m-d\TH:i'), 'meals_planned' => 36, 'is_public' => true,
        ]);
        $batch = $d->fresh()->distributions()->first();
        $svc->completeBatch($batch, $staffUser, 35, null);
        $svc->finishDistribution($d->fresh(), $staffUser);
        $svc->close($d->fresh(), $manager);

        $d = $d->fresh();
        $this->assertSame('Completed', $d->status);
        $this->assertSame(['Submitted', 'Assigned', 'Verified', 'Approved', 'Collected', 'Distributed', 'Completed'], $d->histories->pluck('to_status')->all());
        $this->assertNull($d->histories->first()->from_status);
        $this->assertGreaterThan(0, $this->donor()->user->notificationsList()->count());
        $this->assertDatabaseHas('audit_logs', ['action_type' => 'DONATION_COMPLETED']);
    }

    public function test_states_cannot_be_skipped_and_roles_are_enforced(): void
    {
        $this->seedDemo();
        $svc = app(DonationService::class);
        $manager = $this->user('manager@kyaf.local');
        $d = $this->makeDonation();

        // cannot approve a donation that is not Verified
        $this->expectException(DomainException::class);
        try {
            $svc->decide($d, $manager, 'Approved', null);
        } finally {
            $this->assertSame('Submitted', $d->fresh()->status);
        }
    }

    public function test_only_the_assigned_staff_member_may_verify(): void
    {
        $this->seedDemo();
        $svc = app(DonationService::class);
        $d = $this->makeDonation();
        $svc->assign($d, $this->staff(1)->staff_id, $this->user('manager@kyaf.local'));
        $this->expectException(DomainException::class);
        $svc->verify($d->fresh(), $this->user('staff2@kyaf.local'), 'Verified', 'Good', 10, null);
    }

    public function test_rejection_requires_a_reason_and_is_shown_in_history(): void
    {
        $this->seedDemo();
        $svc = app(DonationService::class);
        $d = $this->makeDonation();
        $svc->assign($d, $this->staff(1)->staff_id, $this->user('manager@kyaf.local'));
        try {
            $svc->verify($d->fresh(), $this->user('staff1@kyaf.local'), 'Rejected', 'Unsafe', 0, '');
            $this->fail('A rejection without a reason must fail');
        } catch (DomainException $e) {
            $this->assertSame('Assigned', $d->fresh()->status);
        }
        $svc->verify($d->fresh(), $this->user('staff1@kyaf.local'), 'Rejected', 'Unsafe', 0, 'খাবার নষ্ট');
        $this->assertSame('Rejected', $d->fresh()->status);
        $this->assertSame('খাবার নষ্ট', $d->fresh()->histories->last()->remarks);
    }

    public function test_collected_quantity_cannot_exceed_verified_quantity(): void
    {
        $this->seedDemo();
        $svc = app(DonationService::class);
        $m = $this->user('manager@kyaf.local');
        $s = $this->user('staff1@kyaf.local');
        $d = $this->makeDonation();
        $svc->assign($d, $this->staff(1)->staff_id, $m);
        $svc->verify($d->fresh(), $s, 'Verified', 'Good', 30, null);
        $svc->decide($d->fresh(), $m, 'Approved', null);
        $this->expectException(DomainException::class);
        $svc->collect($d->fresh(), $s, 31, null);
    }

    public function test_expired_food_is_never_verified_and_becomes_expired(): void
    {
        $this->seedDemo();
        $svc = app(DonationService::class);
        $d = $this->makeDonation();
        $svc->assign($d, $this->staff(1)->staff_id, $this->user('manager@kyaf.local'));
        $this->travelTo(now()->addDays(3));
        try {
            $svc->verify($d->fresh(), $this->user('staff1@kyaf.local'), 'Verified', 'Good', 10, null);
            $this->fail('Expired food must not be verified');
        } catch (DomainException $e) {
            $this->assertSame('Expired', $d->fresh()->status);
        }
    }

    public function test_scheduler_command_expires_overdue_donations(): void
    {
        $this->seedDemo();
        $d = $this->makeDonation();
        $this->travelTo(now()->addDays(3));
        $this->artisan('kyaf:expire-donations')->assertSuccessful();
        $this->assertSame('Expired', $d->fresh()->status);
    }

    public function test_submission_is_refused_when_pickup_thana_has_no_service_area(): void
    {
        $this->seedDemo();
        $uncovered = \App\Models\Thana::whereNull('area_id')->firstOrFail();
        $this->expectException(DomainException::class);
        $this->makeDonation(['pickup_thana_id' => $uncovered->thana_id]);
    }

    public function test_expiry_must_be_at_least_two_hours_ahead(): void
    {
        $this->seedDemo();
        $soon = now()->timezone('Asia/Dhaka')->addMinutes(30);
        $this->expectException(DomainException::class);
        $this->makeDonation(['expiry_date' => $soon->toDateString(), 'expiry_time' => $soon->format('H:i')]);
    }

    public function test_identical_donation_within_ten_minutes_needs_confirmation(): void
    {
        $this->seedDemo();
        $svc = app(DonationService::class);
        $this->makeDonation();
        $this->assertSame('duplicate', $svc->submit($this->donor(), $this->donationData(), null, false));
        $this->assertInstanceOf(Donation::class, $svc->submit($this->donor(), $this->donationData(), null, true));
    }

    public function test_donor_can_cancel_until_collected_with_a_reason_only(): void
    {
        $this->seedDemo();
        $svc = app(DonationService::class);
        $d = $this->makeDonation();
        try {
            $svc->cancel($d, $this->donor()->user, '');
            $this->fail('cancellation needs a reason');
        } catch (DomainException $e) {
        }
        $svc->cancel($d, $this->donor()->user, 'আর প্রয়োজন নেই');
        $this->assertSame('Cancelled', $d->fresh()->status);
    }

    public function test_admin_override_requires_a_reason_and_is_logged_as_override(): void
    {
        $this->seedDemo();
        $svc = app(DonationService::class);
        $admin = $this->user('admin@kyaf.local');
        $d = $this->makeDonation();
        try {
            $svc->assign($d, $this->staff(1)->staff_id, $admin, null);
            $this->fail('override without reason must fail');
        } catch (DomainException $e) {
        }
        $svc->assign($d, $this->staff(1)->staff_id, $admin, 'ম্যানেজার অনুপস্থিত');
        $this->assertDatabaseHas('audit_logs', ['action_type' => 'OVERRIDE_ASSIGNED']);
    }
}
