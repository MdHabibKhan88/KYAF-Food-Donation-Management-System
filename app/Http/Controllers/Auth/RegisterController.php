<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Donor;
use App\Models\Role;
use App\Models\UserAccount;
use App\Rules\BdPhone;
use App\Rules\StrongPassword;
use App\Services\Audit;
use App\Services\Notifier;
use App\Services\PasswordPolicy;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->merge([
            'phone' => Phone::normalize($request->input('phone')) ?? $request->input('phone'),
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
        $data = $request->validate([
            'full_name' => 'required|string|min:2|max:100',
            'email' => 'required|email:rfc|max:150|unique:user_accounts,email',
            'phone' => ['required', new BdPhone, 'unique:user_accounts,phone'],
            'donor_type' => 'required|in:Individual,Restaurant,Organization',
            'organization_name' => 'required_if:donor_type,Restaurant,Organization|nullable|string|max:120',
            'thana_id' => 'required|exists:thanas,thana_id',
            'address_line' => 'required|string|max:250',
            'password' => ['required', 'confirmed', new StrongPassword],
            'terms' => 'accepted',
        ], [
            'email.unique' => 'এই ইমেইল দিয়ে ইতোমধ্যে একটি অ্যাকাউন্ট আছে।',
            'phone.unique' => 'এই মোবাইল নম্বর দিয়ে ইতোমধ্যে একটি অ্যাকাউন্ট আছে।',
            'terms.accepted' => 'নিবন্ধনের জন্য শর্তাবলি গ্রহণ করতে হবে।',
            'organization_name.required_if' => 'রেস্তোরাঁ বা প্রতিষ্ঠানের নাম আবশ্যক।',
        ]);

        $donor = DB::transaction(function () use ($data) {
            $user = UserAccount::create([
                'role_id' => Role::where('role_name', Role::DONOR)->value('role_id'),
                'full_name' => $data['full_name'], 'email' => $data['email'], 'phone' => $data['phone'],
                'password_hash' => 'pending', 'status' => 'Active',
            ]);
            PasswordPolicy::set($user, $data['password']);

            return Donor::create([
                'donor_id' => Donor::nextId(), 'user_id' => $user->user_id, 'donor_type' => $data['donor_type'],
                'organization_name' => $data['donor_type'] === 'Individual' ? null : $data['organization_name'],
                'thana_id' => $data['thana_id'], 'address_line' => $data['address_line'], 'registered_at' => now(),
            ]);
        });

        Audit::log('DONOR_REGISTER', 'Donor', $donor->donor_id, null, ['email' => $data['email']], 'Success', $donor->user_id);
        Notifier::to($donor->user_id, 'WELCOME', 'স্বাগতম!', "আপনার দাতা আইডি: {$donor->donor_id}। এখন লগইন করে খাবার দান করুন।");

        return redirect()->route('login.show', 'donor')->with('success', "নিবন্ধন সফল হয়েছে! আপনার দাতা আইডি: {$donor->donor_id}। এখন লগইন করুন।");
    }
}
