<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\Audit;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /** key => [Bangla label, validation rule]. TEAM_SIZE (BR-01) and reset-token lifetime (BR-27) are fixed by the SRS and not editable. */
    public const FIELDS = [
        'SESSION_TIMEOUT_MIN' => ['নিষ্ক্রিয়তার পর সেশন শেষ (মিনিট)', 'required|integer|min:5|max:480'],
        'LOGIN_MAX_ATTEMPTS' => ['লগইন ব্যর্থ হওয়ার সর্বোচ্চ সংখ্যা', 'required|integer|min:3|max:10'],
        'LOGIN_LOCK_MINUTES' => ['অ্যাকাউন্ট লকের সময় (মিনিট)', 'required|integer|min:5|max:120'],
        'MIN_EXPIRY_LEAD_HOURS' => ['খাবারের মেয়াদের ন্যূনতম অগ্রিম সময় (ঘণ্টা)', 'required|integer|min:1|max:24'],
        'INVITE_VALID_HOURS' => ['স্টাফ আমন্ত্রণ লিংকের বৈধতা (ঘণ্টা)', 'required|integer|min:24|max:168'],
        'MAX_MEALS' => ['এক দানে সর্বোচ্চ মিল', 'required|integer|min:100|max:1000000'],
        'CONTACT_PHONE' => ['যোগাযোগের মোবাইল নম্বর (প্রদর্শনের জন্য)', 'required|string|max:40'],
        'CONTACT_EMAIL' => ['যোগাযোগের ইমেইল', 'required|email|max:150'],
        'CONTACT_ADDRESS' => ['যোগাযোগের ঠিকানা', 'required|string|max:200'],
    ];

    public function edit()
    {
        $values = collect(self::FIELDS)->map(fn ($f, $k) => setting($k));

        return view('admin.settings', ['fields' => self::FIELDS, 'values' => $values]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(collect(self::FIELDS)->map(fn ($f) => $f[1])->all());
        $old = [];
        foreach ($data as $k => $v) {
            $old[$k] = setting($k);
            SystemSetting::updateOrCreate(['setting_key' => $k], [
                'setting_value' => (string) $v, 'description' => self::FIELDS[$k][0], 'updated_by' => $request->user()->user_id, 'updated_at' => now(),
            ]);
        }
        flush_settings_cache();
        Audit::log('SETTINGS_UPDATE', 'SystemSetting', null, $old, $data);

        return back()->with('success', 'সেটিংস সংরক্ষিত হয়েছে।');
    }
}
