<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\Collection;
use App\Models\Distribution;
use App\Models\Donation;
use App\Models\DonationStatusHistory;
use App\Models\Donor;
use App\Models\Staff;
use App\Models\Thana;
use App\Models\UserAccount;
use App\Models\Verification;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Donation lifecycle (SRS Appendix B state diagram, BR-12 .. BR-19, BR-32, BR-36).
 * All status changes go through transition() so history, audit and notifications can never be skipped.
 */
class DonationService
{
    /** from => allowed targets (every move is further restricted by role in the public methods). */
    private const FLOW = [
        'Submitted' => ['Assigned', 'Cancelled', 'Expired'],
        'Assigned' => ['Verified', 'Rejected', 'Cancelled', 'Expired'],
        'Verified' => ['Approved', 'Rejected', 'Cancelled', 'Expired'],
        'Approved' => ['Collected', 'Cancelled', 'Expired'],
        'Collected' => ['Distributed'],
        'Distributed' => ['Completed'],
    ];

    // ---------- submission ----------

    /** @return Donation|string  the string 'duplicate' when BR-36 confirmation is required */
    public function submit(Donor $donor, array $data, ?UploadedFile $photo, bool $confirmedDuplicate = false)
    {
        $pickup = Thana::with('serviceArea.manager.user')->find($data['pickup_thana_id']);
        $area = $pickup?->serviceArea;
        if (! $area || $area->status !== 'Active' || ! $area->manager || $area->manager->status !== 'Active' || $area->manager->user->status !== 'Active') {
            Audit::log('DONATION_SUBMIT', 'Donation', null, ['thana' => $data['pickup_thana_id']], null, 'Failure');
            throw new DomainException('দুঃখিত, এই থানা এলাকায় বর্তমানে কোনো সক্রিয় সেবা এলাকা বা এরিয়া ম্যানেজার নেই। অনুগ্রহ করে অন্য এলাকা নির্বাচন করুন।');
        }

        $expiry = Carbon::parse($data['expiry_date'].' '.$data['expiry_time'], config('app.display_timezone'))->utc();
        $lead = (int) setting('MIN_EXPIRY_LEAD_HOURS', 2);
        if ($expiry->lessThan(now()->addHours($lead))) {
            throw new DomainException("মেয়াদ উত্তীর্ণের সময় এখন থেকে কমপক্ষে ".bn_digits($lead)." ঘণ্টা পরে হতে হবে।");
        }

        if (! $confirmedDuplicate) {
            $dup = Donation::where('donor_id', $donor->donor_id)
                ->where('food_name', $data['food_name'])
                ->whereDate('expiry_date', $data['expiry_date'])
                ->where('expiry_time', $data['expiry_time'].':00')
                ->where('pickup_address', $data['pickup_address'])
                ->where('submitted_at', '>=', now()->subMinutes(10))
                ->exists();
            if ($dup) {
                return 'duplicate';
            }
        }

        $path = $photo ? $photo->store('donations', 'local') : null;

        return DB::transaction(function () use ($donor, $data, $area, $path) {
            $year = now()->timezone(config('app.display_timezone'))->format('Y');
            $last = Donation::where('donation_code', 'like', "DON-{$year}-%")->lockForUpdate()->orderByDesc('donation_code')->value('donation_code');
            $n = $last ? ((int) substr($last, -6)) + 1 : 1;
            $code = "DON-{$year}-".str_pad((string) $n, 6, '0', STR_PAD_LEFT);

            $d = Donation::create([
                'donation_code' => $code,
                'donor_id' => $donor->donor_id,
                'area_id' => $area->area_id,
                'food_name' => $data['food_name'],
                'food_category' => $data['food_category'],
                'quantity_meals' => (int) $data['quantity_meals'],
                'expiry_date' => $data['expiry_date'],
                'expiry_time' => $data['expiry_time'].':00',
                'description' => $data['description'] ?? null,
                'pickup_thana_id' => $data['pickup_thana_id'],
                'pickup_address' => $data['pickup_address'],
                'contact_phone' => $data['contact_phone'],
                'photo_path' => $path,
                'status' => 'Submitted',
            ]);
            $this->history($d, null, 'Submitted', auth()->id(), null);
            Audit::log('DONATION_SUBMIT', 'Donation', $d->donation_id, null, ['code' => $code, 'meals' => $d->quantity_meals]);
            Notifier::to($donor->user_id, 'DONATION_STATUS', 'দান জমা হয়েছে', "আপনার দান {$code} সফলভাবে জমা হয়েছে। ট্র্যাকিং কোড: {$code}", $d->donation_id);
            Notifier::to($area->manager->user_id, 'DONATION_STATUS', 'নতুন দান জমা হয়েছে', "আপনার এলাকায় নতুন দান {$code} জমা হয়েছে। স্টাফ নিয়োগ করুন।", $d->donation_id);

            return $d;
        });
    }

    // ---------- manager / admin: assignment ----------

    public function assign(Donation $donation, int $staffId, UserAccount $actor, ?string $overrideReason = null): void
    {
        $this->expiryGuard($donation);
        DB::transaction(function () use ($donation, $staffId, $actor, $overrideReason) {
            $d = $this->lock($donation);
            $override = $this->requireManagerOrAdmin($d, $actor, $overrideReason);
            if ($d->status !== 'Submitted') {
                throw new DomainException('শুধু "জমা দেওয়া" অবস্থার দানে স্টাফ নিয়োগ করা যায়।');
            }
            $staff = Staff::with('manager')->where('status', 'Active')->find($staffId);
            // BR-14: only Active Staff of the area's own manager team
            if (! $staff || $staff->manager_id !== optional($d->area)->manager_id || $staff->user->status !== 'Active') {
                throw new DomainException('শুধু এই এলাকার ম্যানেজারের দলের সক্রিয় স্টাফকে নিয়োগ দেওয়া যায়।');
            }
            $d->assigned_staff_id = $staff->staff_id;
            $this->transition($d, 'Assigned', $actor, $override ? "ওভাররাইড: {$overrideReason}" : null, $override);
            Notifier::to($staff->user_id, 'DONATION_ASSIGNED', 'নতুন দায়িত্ব', "আপনাকে দান {$d->donation_code} যাচাইয়ের জন্য নিয়োগ দেওয়া হয়েছে।", $d->donation_id);
        });
    }

    // ---------- staff: verification ----------

    public function verify(Donation $donation, UserAccount $actor, string $result, string $condition, int $qty, ?string $remarks): void
    {
        $this->expiryGuard($donation);
        DB::transaction(function () use ($donation, $actor, $result, $condition, $qty, $remarks) {
            $d = $this->lock($donation);
            $staff = $this->requireAssignedStaff($d, $actor);
            if ($d->status !== 'Assigned') {
                throw new DomainException('এই দানটি এখন যাচাইয়ের অবস্থায় নেই।');
            }
            if ($result === 'Rejected' && ! filled($remarks)) {
                throw new DomainException('প্রত্যাখ্যানের কারণ লিখতে হবে।');
            }
            if ($result === 'Verified') {
                if ($condition === 'Unsafe') {
                    throw new DomainException('অনিরাপদ খাবার যাচাই করা যাবে না; প্রত্যাখ্যান করুন।');
                }
                if ($qty < 1 || $qty > $d->quantity_meals) {
                    throw new DomainException('যাচাইকৃত পরিমাণ ১ থেকে দাতার ঘোষিত পরিমাণের মধ্যে হতে হবে।');
                }
            }
            Verification::create([
                'donation_id' => $d->donation_id, 'staff_id' => $staff->staff_id, 'result' => $result,
                'food_condition' => $condition, 'verified_quantity_meals' => $result === 'Verified' ? $qty : 0,
                'remarks' => $remarks, 'verified_at' => now(),
            ]);
            $this->transition($d, $result === 'Verified' ? 'Verified' : 'Rejected', $actor, $remarks);
        });
    }

    // ---------- manager / admin: approval ----------

    public function decide(Donation $donation, UserAccount $actor, string $decision, ?string $remarks, ?string $overrideReason = null): void
    {
        if ($decision === 'Approved') {
            $this->expiryGuard($donation);
        }
        DB::transaction(function () use ($donation, $actor, $decision, $remarks, $overrideReason) {
            $d = $this->lock($donation);
            $override = $this->requireManagerOrAdmin($d, $actor, $overrideReason);
            if ($d->status !== 'Verified') {
                throw new DomainException('অনুমোদনের আগে দানটি অবশ্যই "যাচাইকৃত" হতে হবে।');
            }
            if ($decision === 'Rejected' && ! filled($remarks)) {
                throw new DomainException('প্রত্যাখ্যানের কারণ লিখতে হবে।');
            }
            Approval::create(['donation_id' => $d->donation_id, 'decided_by' => $actor->user_id, 'decision' => $decision, 'remarks' => $remarks, 'decided_at' => now()]);
            $this->transition($d, $decision, $actor, $override ? trim("ওভাররাইড: {$overrideReason}. {$remarks}") : $remarks, $override);

            if ($decision === 'Approved') {
                $verified = $d->verifiedQuantity() ?? $d->quantity_meals;
                $when = now()->addHour();
                $cap = $d->expiryInstant()->subMinutes(15);
                if ($when->greaterThan($cap)) {
                    $when = now();
                }
                Collection::create(['donation_id' => $d->donation_id, 'staff_id' => $d->assigned_staff_id, 'scheduled_at' => $when, 'status' => 'Scheduled']);
                Notifier::to($d->assignedStaff->user_id, 'COLLECTION_DUE', 'সংগ্রহ করুন', "দান {$d->donation_code} অনুমোদিত হয়েছে। খাবার সংগ্রহ করুন (পরিমাণ: ".bn_digits($verified).' মিল)।', $d->donation_id);
            }
        });
    }

    // ---------- staff: collection ----------

    public function collect(Donation $donation, UserAccount $actor, int $qty, ?string $remarks, bool $failed = false): void
    {
        $this->expiryGuard($donation);
        DB::transaction(function () use ($donation, $actor, $qty, $remarks, $failed) {
            $d = $this->lock($donation);
            $this->requireAssignedStaff($d, $actor);
            if ($d->status !== 'Approved') {
                throw new DomainException('শুধু অনুমোদিত দান সংগ্রহ করা যায়।');
            }
            $col = Collection::where('donation_id', $d->donation_id)->lockForUpdate()->first();
            if (! $col) {
                throw new DomainException('সংগ্রহের সূচি পাওয়া যায়নি।');
            }
            if ($failed) {
                if (! filled($remarks)) {
                    throw new DomainException('সংগ্রহ ব্যর্থ হলে কারণ লিখতে হবে।');
                }
                $col->update(['status' => 'Failed', 'remarks' => $remarks]);
                Audit::log('COLLECTION_FAILED', 'Donation', $d->donation_id, null, ['remarks' => $remarks]);
                Notifier::to(optional($d->area->manager)->user_id, 'COLLECTION_FAILED', 'সংগ্রহ ব্যর্থ', "দান {$d->donation_code} সংগ্রহ করা যায়নি: {$remarks}", $d->donation_id);

                return;
            }
            $verified = $d->verifiedQuantity() ?? $d->quantity_meals;
            if ($qty < 1 || $qty > $verified) {
                throw new DomainException('সংগৃহীত পরিমাণ যাচাইকৃত পরিমাণ ('.bn_digits($verified).') এর বেশি হতে পারে না।');
            }
            $col->update(['status' => 'Collected', 'collected_at' => now(), 'collected_quantity_meals' => $qty, 'remarks' => $remarks]);
            $this->transition($d, 'Collected', $actor, $remarks);
        });
    }

    // ---------- staff: distribution ----------

    public function planDistribution(Donation $donation, UserAccount $actor, array $data): Distribution
    {
        $this->expiryGuard($donation);

        return DB::transaction(function () use ($donation, $actor, $data) {
            $d = $this->lock($donation);
            $staff = $this->requireAssignedStaff($d, $actor);
            if ($d->status !== 'Collected') {
                throw new DomainException('খাবার সংগ্রহের পর বিতরণ পরিকল্পনা করা যায়।');
            }
            $collected = (int) $d->collection->collected_quantity_meals;
            $planned = (int) $d->distributions()->where('status', '!=', 'Cancelled')->sum('meals_planned');
            if ($planned + (int) $data['meals_planned'] > $collected) {
                throw new DomainException('মোট পরিকল্পিত মিল সংগৃহীত পরিমাণ ('.bn_digits($collected).') এর বেশি হতে পারে না।');
            }
            if (Carbon::parse($data['scheduled_end'])->lessThanOrEqualTo(Carbon::parse($data['scheduled_start']))) {
                throw new DomainException('শেষ সময় শুরুর সময়ের পরে হতে হবে।');
            }
            $dist = Distribution::create([
                'donation_id' => $d->donation_id, 'staff_id' => $staff->staff_id,
                'point_name' => $data['point_name'], 'point_address' => $data['point_address'], 'thana_id' => $data['thana_id'],
                'scheduled_start' => Carbon::parse($data['scheduled_start'], config('app.display_timezone'))->utc(),
                'scheduled_end' => Carbon::parse($data['scheduled_end'], config('app.display_timezone'))->utc(),
                'meals_planned' => (int) $data['meals_planned'], 'is_public' => (bool) ($data['is_public'] ?? false),
                'status' => 'Planned', 'remarks' => $data['remarks'] ?? null,
            ]);
            Audit::log('DISTRIBUTION_PLAN', 'Distribution', $dist->distribution_id, null, ['donation' => $d->donation_code, 'meals' => $dist->meals_planned]);

            return $dist;
        });
    }

    public function completeBatch(Distribution $batch, UserAccount $actor, int $meals, ?string $remarks): void
    {
        $this->expiryGuard($batch->donation);
        DB::transaction(function () use ($batch, $actor, $meals, $remarks) {
            $d = $this->lock($batch->donation);
            $this->requireAssignedStaff($d, $actor);
            $b = Distribution::lockForUpdate()->findOrFail($batch->distribution_id);
            if ($d->status !== 'Collected' || in_array($b->status, ['Completed', 'Cancelled'], true)) {
                throw new DomainException('এই বিতরণ ব্যাচ আর হালনাগাদ করা যাবে না।');
            }
            if ($meals < 0 || $meals > $b->meals_planned) {
                throw new DomainException('বিতরণকৃত মিল পরিকল্পিত পরিমাণের বেশি হতে পারে না।');
            }
            $collected = (int) $d->collection->collected_quantity_meals;
            $already = (int) $d->distributions()->where('distribution_id', '!=', $b->distribution_id)->sum('meals_distributed');
            if ($already + $meals > $collected) {
                throw new DomainException('মোট বিতরণ সংগৃহীত পরিমাণের বেশি হতে পারে না।');
            }
            $b->update(['status' => 'Completed', 'meals_distributed' => $meals, 'completed_at' => now(), 'remarks' => $remarks ?: $b->remarks]);
            Audit::log('DISTRIBUTION_DONE', 'Distribution', $b->distribution_id, null, ['meals' => $meals]);
        });
    }

    public function cancelBatch(Distribution $batch, UserAccount $actor): void
    {
        DB::transaction(function () use ($batch, $actor) {
            $d = $this->lock($batch->donation);
            $this->requireAssignedStaff($d, $actor);
            if ($d->status !== 'Collected' || $batch->status !== 'Planned') {
                throw new DomainException('শুধু পরিকল্পিত ব্যাচ বাতিল করা যায়।');
            }
            $batch->update(['status' => 'Cancelled']);
            Audit::log('DISTRIBUTION_CANCEL', 'Distribution', $batch->distribution_id);
        });
    }

    public function finishDistribution(Donation $donation, UserAccount $actor): void
    {
        $this->expiryGuard($donation);
        DB::transaction(function () use ($donation, $actor) {
            $d = $this->lock($donation);
            $this->requireAssignedStaff($d, $actor);
            if ($d->status !== 'Collected') {
                throw new DomainException('দানটি এখন বিতরণ সমাপ্ত করার অবস্থায় নেই।');
            }
            if ($d->distributions()->where('status', 'Planned')->exists() || $d->distributions()->where('status', 'Ongoing')->exists()) {
                throw new DomainException('সব ব্যাচ সম্পন্ন বা বাতিল করার পর বিতরণ সমাপ্ত করুন।');
            }
            if (! $d->distributions()->where('status', 'Completed')->where('meals_distributed', '>', 0)->exists()) {
                throw new DomainException('অন্তত একটি সম্পন্ন বিতরণ ব্যাচ থাকতে হবে।');
            }
            $this->transition($d, 'Distributed', $actor, null);
        });
    }

    // ---------- manager / admin: close ----------

    public function close(Donation $donation, UserAccount $actor, ?string $overrideReason = null): void
    {
        DB::transaction(function () use ($donation, $actor, $overrideReason) {
            $d = $this->lock($donation);
            $override = $this->requireManagerOrAdmin($d, $actor, $overrideReason);
            if ($d->status !== 'Distributed') {
                throw new DomainException('শুধু বিতরিত দান সম্পন্ন করা যায়।');
            }
            $this->transition($d, 'Completed', $actor, $override ? "ওভাররাইড: {$overrideReason}" : null, $override);
        });
    }

    // ---------- donor / admin: cancel ----------

    public function cancel(Donation $donation, UserAccount $actor, string $reason): void
    {
        if (! filled($reason)) {
            throw new DomainException('বাতিলের কারণ লিখতে হবে।');
        }
        DB::transaction(function () use ($donation, $actor, $reason) {
            $d = $this->lock($donation);
            $d->loadMissing('donor');
            $override = false;
            if ($actor->hasRole('DONOR')) {
                if (optional($d->donor)->user_id !== $actor->user_id) {
                    $this->deny($actor, $d);
                }
            } elseif ($actor->hasRole('ADMIN')) {
                $override = true;
            } else {
                $this->deny($actor, $d);
            }
            if (! in_array($d->status, ['Submitted', 'Assigned', 'Verified', 'Approved'], true)) {
                throw new DomainException('সংগ্রহের পর বা চূড়ান্ত অবস্থার দান বাতিল করা যায় না।');
            }
            $this->transition($d, 'Cancelled', $actor, $reason, $override);
            if ($d->assignedStaff) {
                Notifier::to($d->assignedStaff->user_id, 'DONATION_CANCELLED', 'দান বাতিল', "দান {$d->donation_code} বাতিল হয়েছে: {$reason}", $d->donation_id);
            }
        });
    }

    // ---------- system: automatic expiry (BR-17) ----------

    public function expireOverdue(): int
    {
        $n = 0;
        Donation::whereIn('status', ['Submitted', 'Assigned', 'Verified', 'Approved'])
            ->whereDate('expiry_date', '<=', now()->timezone(config('app.display_timezone'))->addDay()->toDateString())
            ->get()
            ->each(function (Donation $d) use (&$n) {
                if ($d->isExpired()) {
                    $this->expire($d);
                    $n++;
                }
            });

        return $n;
    }

    // ---------- internals ----------

    /** Re-check expiry (BR-17). Expires the donation in its own committed transaction, then aborts the action. */
    private function expiryGuard(Donation $donation): void
    {
        if (! in_array($donation->status, ['Submitted', 'Assigned', 'Verified', 'Approved', 'Collected'], true)) {
            return;
        }
        if ($donation->isExpired()) {
            if ($donation->status !== 'Collected') {
                $this->expire($donation);
            }
            throw new DomainException('খাবারের মেয়াদ উত্তীর্ণ হয়ে গেছে; এই কাজ করা যাবে না।');
        }
    }

    private function expire(Donation $donation): void
    {
        DB::transaction(function () use ($donation) {
            $d = $this->lock($donation);
            if (in_array($d->status, ['Submitted', 'Assigned', 'Verified', 'Approved'], true)) {
                $this->transition($d, 'Expired', null, 'মেয়াদ উত্তীর্ণ (স্বয়ংক্রিয়)');
            }
        });
    }

    private function lock(Donation $donation): Donation
    {
        return Donation::with('area.manager.user', 'assignedStaff.user', 'donor.user')->lockForUpdate()->findOrFail($donation->donation_id);
    }

    /** Validate the move against the lifecycle, persist, then write history, audit and notifications. */
    private function transition(Donation $d, string $to, ?UserAccount $actor, ?string $remarks, bool $override = false): void
    {
        $from = $d->status;
        if (! in_array($to, self::FLOW[$from] ?? [], true)) {
            throw new DomainException("অবৈধ অবস্থা পরিবর্তন: {$from} → {$to}");
        }
        $d->status = $to;
        $d->save();
        $this->history($d, $from, $to, $actor?->user_id, $remarks);
        Audit::log($override ? 'OVERRIDE_'.strtoupper($to) : 'DONATION_'.strtoupper($to), 'Donation', $d->donation_id, ['status' => $from], ['status' => $to, 'remarks' => $remarks], 'Success', $actor?->user_id);
        $label = __('kyaf.status.'.$to);
        $msg = "দান {$d->donation_code} এর অবস্থা এখন: {$label}".($remarks && in_array($to, ['Rejected', 'Cancelled'], true) ? " — কারণ: {$remarks}" : '');
        Notifier::donation($d, 'দানের অবস্থা পরিবর্তন', $msg, $actor ? [] : []);
    }

    private function history(Donation $d, ?string $from, string $to, ?int $by, ?string $remarks): void
    {
        DonationStatusHistory::create([
            'donation_id' => $d->donation_id, 'from_status' => $from, 'to_status' => $to,
            'changed_by' => $by, 'remarks' => $remarks, 'changed_at' => now(),
        ]);
    }

    /** Area manager of the donation's area, or Admin (override needs a reason — BR-32). Returns true when it is an override. */
    private function requireManagerOrAdmin(Donation $d, UserAccount $actor, ?string $overrideReason): bool
    {
        if ($actor->hasRole('ADMIN')) {
            if (! filled($overrideReason)) {
                throw new DomainException('অ্যাডমিন ওভাররাইডের জন্য কারণ লিখতে হবে।');
            }

            return true;
        }
        if ($actor->hasRole('AREA_MANAGER') && optional($actor->manager)->manager_id && $actor->manager->manager_id === optional($d->area)->manager_id) {
            return false;
        }
        $this->deny($actor, $d);
    }

    /** BR-14: only the assigned staff member. */
    private function requireAssignedStaff(Donation $d, UserAccount $actor): Staff
    {
        $staff = $actor->hasRole('STAFF') ? $actor->staff : null;
        if (! $staff || $staff->status !== 'Active' || $d->assigned_staff_id !== $staff->staff_id) {
            $this->deny($actor, $d);
        }

        return $staff;
    }

    private function deny(UserAccount $actor, Donation $d): never
    {
        Audit::log('ACCESS_DENIED', 'Donation', $d->donation_id, null, ['role' => $actor->roleName()], 'Failure', $actor->user_id);
        throw new DomainException('এই কাজটি করার অনুমতি আপনার নেই।');
    }
}
