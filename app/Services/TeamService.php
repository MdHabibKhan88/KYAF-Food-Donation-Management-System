<?php

namespace App\Services;

use App\Models\AreaManager;
use App\Models\Staff;
use App\Models\StaffApplication;
use DomainException;
use Illuminate\Support\Facades\DB;

/** BR-01 / BR-03: exactly five Staff per Area Manager, never a sixth Active member. */
class TeamService
{
    public static function capacity(): int
    {
        return (int) setting('TEAM_SIZE', 5);
    }

    /** Active staff + still-valid approved invitations (they reserve a seat). */
    public function reservedSeats(AreaManager $manager): int
    {
        $area = $manager->serviceArea;
        $invites = $area ? StaffApplication::where('area_id', $area->area_id)
            ->where('status', 'Approved')->where('invitation_expires_at', '>', now())->count() : 0;

        return $manager->activeStaffCount() + $invites;
    }

    public function hasVacancy(AreaManager $manager): bool
    {
        return $this->reservedSeats($manager) < self::capacity();
    }

    /** Must be called inside a DB transaction; locks the manager row first. */
    public function addStaff(AreaManager $manager, int $userId, ?int $applicationId): Staff
    {
        $locked = AreaManager::lockForUpdate()->findOrFail($manager->manager_id);
        if ($locked->activeStaffCount() >= self::capacity()) {
            throw new DomainException('এই এরিয়া ম্যানেজারের দলে ইতোমধ্যে '.bn_digits(self::capacity()).' জন সক্রিয় স্টাফ আছেন। ষষ্ঠ স্টাফ যোগ করা যাবে না।');
        }

        return Staff::create([
            'user_id' => $userId, 'manager_id' => $locked->manager_id, 'application_id' => $applicationId,
            'joined_at' => now(), 'status' => 'Active',
        ]);
    }

    public function reactivate(Staff $staff): void
    {
        DB::transaction(function () use ($staff) {
            $manager = AreaManager::lockForUpdate()->findOrFail($staff->manager_id);
            if ($manager->activeStaffCount() >= self::capacity()) {
                throw new DomainException('দলে আর জায়গা নেই; একজন সক্রিয় স্টাফ সরানোর পর আবার চেষ্টা করুন।');
            }
            $staff->update(['status' => 'Active']);
        });
    }
}
