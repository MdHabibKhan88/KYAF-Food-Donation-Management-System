<?php

namespace App\Services;

use App\Models\StaffApplication;
use App\Models\UserAccount;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/** Staff application review (BR-20, BR-21, BR-22). Used by Area Managers (own area) and Admin (override with reason). */
class ApplicationService
{
    public function __construct(private TeamService $team)
    {
    }

    /** @return string the plain invitation link (shown once to the reviewer; e-mail delivery is not configured, SRS C-08) */
    public function approve(StaffApplication $app, UserAccount $actor, ?string $overrideReason = null): string
    {
        return DB::transaction(function () use ($app, $actor, $overrideReason) {
            $a = StaffApplication::with('area.manager')->lockForUpdate()->findOrFail($app->application_id);
            $this->authorize($a, $actor, $overrideReason);
            if (! in_array($a->status, ['Submitted', 'UnderReview', 'Expired'], true)) {
                throw new DomainException('এই আবেদনটি আর অনুমোদন করা যায় না।');
            }
            $manager = $a->area->manager;
            if (! $manager || $manager->status !== 'Active') {
                throw new DomainException('এই এলাকায় সক্রিয় এরিয়া ম্যানেজার নেই।');
            }
            if (! $this->team->hasVacancy($manager)) {
                throw new DomainException('এই সেবা এলাকার দলে কোনো শূন্যপদ নেই। শূন্যপদ তৈরি না হওয়া পর্যন্ত আবেদন অনুমোদন করা যাবে না।');
            }
            $plain = Str::random(64);
            $a->update([
                'status' => 'Approved', 'reviewed_by' => $actor->user_id, 'reviewed_at' => now(), 'review_remarks' => $overrideReason,
                'invitation_token_hash' => hash('sha256', $plain),
                'invitation_expires_at' => now()->addHours((int) setting('INVITE_VALID_HOURS', 72)),
            ]);
            $link = route('invitation.show', $plain);
            Log::info("[staff invitation for application {$a->application_id}] {$link}");
            Audit::log($actor->hasRole('ADMIN') ? 'OVERRIDE_APP_APPROVE' : 'APP_APPROVE', 'StaffApplication', $a->application_id, ['status' => $app->status], ['status' => 'Approved', 'reason' => $overrideReason]);

            return $link;
        });
    }

    public function reject(StaffApplication $app, UserAccount $actor, string $remarks, ?string $overrideReason = null): void
    {
        if (! filled($remarks)) {
            throw new DomainException('প্রত্যাখ্যানের কারণ লিখতে হবে।');
        }
        DB::transaction(function () use ($app, $actor, $remarks, $overrideReason) {
            $a = StaffApplication::with('area')->lockForUpdate()->findOrFail($app->application_id);
            $this->authorize($a, $actor, $overrideReason);
            if (! in_array($a->status, ['Submitted', 'UnderReview', 'Approved'], true)) {
                throw new DomainException('এই আবেদনটি আর প্রত্যাখ্যান করা যায় না।');
            }
            $a->update(['status' => 'Rejected', 'reviewed_by' => $actor->user_id, 'reviewed_at' => now(), 'review_remarks' => $remarks, 'invitation_token_hash' => null]);
            Audit::log($actor->hasRole('ADMIN') ? 'OVERRIDE_APP_REJECT' : 'APP_REJECT', 'StaffApplication', $a->application_id, null, ['status' => 'Rejected', 'remarks' => $remarks]);
        });
    }

    public function markUnderReview(StaffApplication $app, UserAccount $actor): void
    {
        $this->authorize($app, $actor, 'review');
        if ($app->status === 'Submitted') {
            $app->update(['status' => 'UnderReview', 'reviewed_by' => $actor->user_id]);
            Audit::log('APP_REVIEW', 'StaffApplication', $app->application_id, ['status' => 'Submitted'], ['status' => 'UnderReview']);
        }
    }

    /** Manager of the target area, or Admin with a reason (BR-32). */
    public function authorize(StaffApplication $a, UserAccount $actor, ?string $overrideReason): void
    {
        if ($actor->hasRole('ADMIN')) {
            if (! filled($overrideReason)) {
                throw new DomainException('অ্যাডমিন ওভাররাইডের জন্য কারণ লিখতে হবে।');
            }

            return;
        }
        $mgr = $actor->hasRole('AREA_MANAGER') ? $actor->manager : null;
        if (! $mgr || $mgr->manager_id !== optional($a->area)->manager_id) {
            Audit::log('ACCESS_DENIED', 'StaffApplication', $a->application_id, null, ['role' => $actor->roleName()], 'Failure');
            throw new DomainException('এই আবেদনটি পর্যালোচনার অনুমতি আপনার নেই।');
        }
    }
}
