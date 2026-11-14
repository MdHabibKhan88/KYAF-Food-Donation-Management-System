<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\UserAccount;
use App\Rules\BdPhone;
use App\Services\Audit;
use App\Services\Notifier;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function show(Request $request)
    {
        return view('public.contact', ['captcha' => $this->newCaptcha($request)]);
    }

    public function store(Request $request)
    {
        $key = 'contact|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()->withInput()->with('error', 'এক ঘণ্টায় সর্বোচ্চ ৫টি বার্তা পাঠানো যায়। পরে আবার চেষ্টা করুন।');
        }
        if ($request->filled('phone')) {
            $request->merge(['phone' => Phone::normalize($request->input('phone')) ?? $request->input('phone')]);
        }
        $data = $request->validate([
            'sender_name' => 'required|string|min:2|max:100',
            'email' => 'required|email:rfc|max:150',
            'phone' => ['nullable', new BdPhone],
            'subject' => 'required|string|min:3|max:150',
            'message_body' => 'required|string|min:10|max:2000',
            'captcha' => 'required|integer',
        ]);
        if ((int) $data['captcha'] !== (int) $request->session()->pull('captcha_answer')) {
            return back()->withInput()->withErrors(['captcha' => 'নিরাপত্তা প্রশ্নের উত্তর সঠিক নয়।']);
        }
        unset($data['captcha']);
        RateLimiter::hit($key, 3600);

        $msg = ContactMessage::create($data + ['status' => 'New']);
        Audit::log('CONTACT_SUBMIT', 'ContactMessage', $msg->message_id, null, ['subject' => $msg->subject], 'Success', null);
        UserAccount::whereHas('role', fn ($r) => $r->where('role_name', 'ADMIN'))->where('status', 'Active')->pluck('user_id')
            ->each(fn ($uid) => Notifier::to($uid, 'CONTACT_MESSAGE', 'নতুন যোগাযোগ বার্তা', "{$msg->sender_name}: {$msg->subject}"));

        return redirect()->route('contact')->with('success', 'আপনার বার্তা সফলভাবে পাঠানো হয়েছে। আমরা শীঘ্রই যোগাযোগ করব।');
    }

    private function newCaptcha(Request $request): string
    {
        $a = random_int(2, 9);
        $b = random_int(2, 9);
        $request->session()->put('captcha_answer', $a + $b);

        return bn_digits($a).' + '.bn_digits($b).' = ?';
    }
}
