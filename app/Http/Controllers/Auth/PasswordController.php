<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\UserAccount;
use App\Rules\StrongPassword;
use App\Services\Audit;
use App\Services\PasswordPolicy;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function forgotForm()
    {
        return view('auth.forgot');
    }

    public function sendLink(Request $request)
    {
        $data = $request->validate(['identifier' => 'required|string|max:150']);
        $phone = Phone::normalize($data['identifier']);
        $user = UserAccount::when($phone, fn ($q) => $q->where('phone', $phone), fn ($q) => $q->where('email', Str::lower(trim($data['identifier']))))->first();

        $devLink = null;
        if ($user && $user->status !== 'Inactive') {
            $plain = Str::random(64);
            PasswordResetToken::create([
                'user_id' => $user->user_id, 'token_hash' => hash('sha256', $plain),
                'expires_at' => now()->addMinutes((int) setting('RESET_VALID_MINUTES', 30)),
            ]);
            $link = route('password.reset', $plain);
            Log::info("[password reset link for user {$user->user_id}] {$link}");
            Audit::log('PASSWORD_RESET_REQUEST', 'UserAccount', $user->user_id, null, null, 'Success', $user->user_id);
            if (app()->environment('local')) {
                $devLink = $link; // no mail server on a local WAMP install, so show the link for testing
            }
        } else {
            Audit::log('PASSWORD_RESET_REQUEST', 'UserAccount', null, ['identifier' => $data['identifier']], null, 'Failure');
        }

        return back()->with('success', 'যদি এই তথ্যের সাথে কোনো অ্যাকাউন্ট থাকে, তবে পাসওয়ার্ড রিসেটের লিংক পাঠানো হয়েছে। লিংকটি ৩০ মিনিট বৈধ থাকবে।')
            ->with('dev_link', $devLink);
    }

    public function resetForm(string $token)
    {
        $row = $this->validToken($token);

        return view('auth.reset', ['token' => $token, 'valid' => (bool) $row]);
    }

    public function reset(Request $request, string $token)
    {
        $row = $this->validToken($token);
        if (! $row) {
            return redirect()->route('password.forgot')->with('error', 'লিংকটি অবৈধ, ব্যবহৃত বা মেয়াদোত্তীর্ণ। নতুন লিংক চান।');
        }
        $data = $request->validate(['password' => ['required', 'confirmed', new StrongPassword]]);
        $user = UserAccount::findOrFail($row->user_id);
        if (PasswordPolicy::reused($user, $data['password'])) {
            return back()->withErrors(['password' => 'নতুন পাসওয়ার্ড আপনার সর্বশেষ তিনটি পাসওয়ার্ডের একটি হতে পারবে না।']);
        }
        DB::transaction(function () use ($user, $row, $data) {
            PasswordPolicy::set($user, $data['password']);
            $user->update(['failed_login_count' => 0, 'locked_until' => null, 'status' => $user->status === 'Locked' ? 'Active' : $user->status]);
            $row->update(['used_at' => now()]);
        });
        Audit::log('PASSWORD_RESET', 'UserAccount', $user->user_id, null, null, 'Success', $user->user_id);

        return redirect()->route('login')->with('success', 'পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে। নতুন পাসওয়ার্ড দিয়ে লগইন করুন।');
    }

    public function change(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', new StrongPassword],
        ]);
        if (! \Illuminate\Support\Facades\Hash::check($data['current_password'], $user->password_hash)) {
            Audit::log('PASSWORD_CHANGE', 'UserAccount', $user->user_id, null, null, 'Failure');

            return back()->withErrors(['current_password' => 'বর্তমান পাসওয়ার্ড সঠিক নয়।']);
        }
        if (PasswordPolicy::reused($user, $data['password'])) {
            return back()->withErrors(['password' => 'নতুন পাসওয়ার্ড আপনার সর্বশেষ তিনটি পাসওয়ার্ডের একটি হতে পারবে না।']);
        }
        PasswordPolicy::set($user, $data['password']);
        Audit::log('PASSWORD_CHANGE', 'UserAccount', $user->user_id);

        return back()->with('success', 'পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে।');
    }

    private function validToken(string $token): ?PasswordResetToken
    {
        return PasswordResetToken::where('token_hash', hash('sha256', $token))
            ->whereNull('used_at')->where('expires_at', '>', now())->first();
    }
}
