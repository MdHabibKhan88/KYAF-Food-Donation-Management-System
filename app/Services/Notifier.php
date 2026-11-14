<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;

class Notifier
{
    /** Create an in-app notification (SRS C-08: e-mail/SMS providers undecided, so e-mail is written to the log only). */
    public static function to(?int $userId, string $type, string $title, string $message, ?int $donationId = null): void
    {
        if (! $userId) {
            return;
        }
        UserNotification::create([
            'user_id' => $userId,
            'donation_id' => $donationId,
            'type' => substr($type, 0, 30),
            'title' => mb_substr($title, 0, 120),
            'message' => mb_substr($message, 0, 500),
            'channel' => 'InApp',
            'delivery_status' => 'Sent',
            'is_read' => false,
        ]);
        Log::info("[notify user {$userId}] {$title}: {$message}");
    }

    /** BR-28: donor + assigned staff + area manager. */
    public static function donation(Donation $d, string $title, string $message, array $exceptUserIds = []): void
    {
        $d->loadMissing('donor', 'area.manager', 'assignedStaff');
        $ids = array_filter([
            $d->donor?->user_id,
            $d->assignedStaff?->user_id,
            $d->area?->manager?->user_id,
        ]);
        foreach (array_unique($ids) as $uid) {
            if (! in_array($uid, $exceptUserIds, true)) {
                self::to($uid, 'DONATION_STATUS', $title, $message, $d->donation_id);
            }
        }
    }
}
