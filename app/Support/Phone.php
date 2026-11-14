<?php

namespace App\Support;

class Phone
{
    /** Accepts 01XXXXXXXXX, 8801XXXXXXXXX, +8801XXXXXXXXX (prefix 013-019). Returns +8801XXXXXXXXX or null. */
    public static function normalize(?string $raw): ?string
    {
        if ($raw === null) {
            return null;
        }
        $s = preg_replace('/[\s\-().]/', '', trim($raw));
        $s = strtr($s, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);
        if (preg_match('/^(?:\+?88)?(01[3-9]\d{8})$/', $s, $m)) {
            return '+88'.$m[1];
        }

        return null;
    }

    /** Display form 01XXX-XXXXXX in Bangla digits. */
    public static function display(?string $stored): string
    {
        if (! $stored) {
            return '—';
        }
        $local = '0'.substr($stored, 4);

        return bn_digits(substr($local, 0, 5).'-'.substr($local, 5));
    }
}
