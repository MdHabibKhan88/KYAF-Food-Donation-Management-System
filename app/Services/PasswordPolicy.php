<?php

namespace App\Services;

use App\Models\PasswordHistory;
use App\Models\UserAccount;
use Illuminate\Support\Facades\Hash;

class PasswordPolicy
{
    /** BR-27: a new password must not equal any of the last three. */
    public static function reused(UserAccount $user, string $plain): bool
    {
        if (Hash::check($plain, $user->password_hash)) {
            return true;
        }
        foreach (PasswordHistory::where('user_id', $user->user_id)->latest('id')->limit(3)->get() as $h) {
            if (Hash::check($plain, $h->password_hash)) {
                return true;
            }
        }

        return false;
    }

    /** Store the new hash and remember it in history. */
    public static function set(UserAccount $user, string $plain): void
    {
        $hash = Hash::make($plain);
        $user->password_hash = $hash;
        $user->save();
        PasswordHistory::create(['user_id' => $user->user_id, 'password_hash' => $hash]);
    }
}
