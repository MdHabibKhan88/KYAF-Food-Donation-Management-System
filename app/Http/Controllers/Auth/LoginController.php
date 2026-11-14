<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserAccount;
use App\Services\Audit;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public const ROLES = ['admin' => 'ADMIN', 'manager' => 'AREA_MANAGER', 'staff' => 'STAFF', 'donor' => 'DONOR'];
    private const GENERIC = 'ইমেইল/মোবাইল নম্বর অথবা পাসওয়ার্ড সঠিক নয়।';

    public function chooser()
    {
        return view('auth.chooser');
    }

    public function show(string $role)
    {
        abort_unless(isset(self::ROLES[$role]), 404);

        return view('auth.login', ['slug' => $role, 'roleName' => self::ROLES[$role]]);
    }

    public function store(Request $request, string $role)
    {
        abort_unless(isset(self::ROLES[$role]), 404);
        $roleName = self::ROLES[$role];
        $data = $request->validate(['identifier' => 'required|string|max:150', 'password' => 'required|string|max:200']);

        $throttleKey = Str::lower($data['identifier']).'|'.$request->ip().'|'.$role;
        if (RateLimiter::tooManyAttempts($throttleKey, 20)) {
            $secs = RateLimiter::availableIn($throttleKey);
            Audit::log('LOGIN', 'UserAccount', null, ['identifier' => $data['identifier']], null, 'Failure');

            return back()->withInput($request->only('identifier'))->with('error', 'অনেকবার চেষ্টা করা হয়েছে। অনুগ্রহ করে '.bn_digits(ceil($secs / 60)).' মিনিট পরে আবার চেষ্টা করুন।');
        }

        $user = $this->find($data['identifier']);

        if ($user) {
            // Lock handling (BR-26)
            if ($user->status === 'Locked' || $user->locked_until) {
                if ($user->locked_until && $user->locked_until->isFuture()) {
                    Audit::log('LOGIN', 'UserAccount', $user->user_id, null, ['reason' => 'locked'], 'Failure', $user->user_id);
                    $mins = (int) ceil(now()->diffInSeconds($user->locked_until, false) / 60);

                    return back()->withInput($request->only('identifier'))->with('error', 'অ্যাকাউন্টটি সাময়িকভাবে লক করা হয়েছে। প্রায় '.bn_digits(max(1, $mins)).' মিনিট পরে আবার চেষ্টা করুন।');
                }
                if ($user->status === 'Locked') {
                    $user->update(['status' => 'Active', 'failed_login_count' => 0, 'locked_until' => null]);
                } else {
                    $user->update(['failed_login_count' => 0, 'locked_until' => null]);
                }
            }
        }

        $ok = $user && Hash::check($data['password'], $user->password_hash);

        if (! $ok) {
            RateLimiter::hit($throttleKey, 600);
            if ($user && $user->status === 'Active') {
                $count = $user->failed_login_count + 1;
                $max = (int) setting('LOGIN_MAX_ATTEMPTS', 5);
                if ($count >= $max) {
                    $user->update(['failed_login_count' => 0, 'status' => 'Locked', 'locked_until' => now()->addMinutes((int) setting('LOGIN_LOCK_MINUTES', 15))]);
                    Audit::log('ACCOUNT_LOCKED', 'UserAccount', $user->user_id, null, null, 'Failure', $user->user_id);
                } else {
                    $user->update(['failed_login_count' => $count]);
                }
            }
            Audit::log('LOGIN', 'UserAccount', $user?->user_id, ['identifier' => $data['identifier'], 'role_page' => $roleName], null, 'Failure', $user?->user_id);

            return back()->withInput($request->only('identifier'))->with('error', self::GENERIC);
        }

        if ($user->status !== 'Active') {
            Audit::log('LOGIN', 'UserAccount', $user->user_id, null, ['reason' => $user->status], 'Failure', $user->user_id);

            return back()->withInput($request->only('identifier'))->with('error', 'এই অ্যাকাউন্টটি সক্রিয় নয়। অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন।');
        }

        // Each login page accepts only its own role; the message stays generic so roles are not revealed.
        if ($user->roleName() !== $roleName) {
            Audit::log('LOGIN', 'UserAccount', $user->user_id, null, ['reason' => 'wrong_role_page', 'page' => $roleName], 'Failure', $user->user_id);

            return back()->withInput($request->only('identifier'))->with('error', self::GENERIC);
        }

        RateLimiter::clear($throttleKey);
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('last_activity', now()->timestamp);
        $user->update(['failed_login_count' => 0, 'locked_until' => null, 'last_login_at' => now()]);
        Audit::log('LOGIN', 'UserAccount', $user->user_id, null, ['role' => $roleName], 'Success', $user->user_id);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Audit::log('LOGOUT', 'UserAccount', Auth::id());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'আপনি সফলভাবে লগআউট হয়েছেন।');
    }

    private function find(string $identifier): ?UserAccount
    {
        $phone = Phone::normalize($identifier);

        return UserAccount::with('role')
            ->when($phone, fn ($q) => $q->where('phone', $phone), fn ($q) => $q->where('email', Str::lower(trim($identifier))))
            ->first();
    }
}
