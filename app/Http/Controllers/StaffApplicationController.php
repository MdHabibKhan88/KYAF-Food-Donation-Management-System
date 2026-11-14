<?php

namespace App\Http\Controllers;

use App\Models\ServiceArea;
use App\Models\StaffApplication;
use App\Models\UserAccount;
use App\Rules\BdPhone;
use App\Rules\Nid;
use App\Services\Audit;
use App\Services\Notifier;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StaffApplicationController extends Controller
{
    public function create()
    {
        $areas = ServiceArea::with('district')->where('status', 'Active')->whereNotNull('manager_id')->orderBy('area_name')->get();

        return view('public.staff-apply', ['areas' => $areas]);
    }

    public function store(Request $request)
    {
        $request->merge([
            'phone' => Phone::normalize($request->input('phone')) ?? $request->input('phone'),
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
        $data = $request->validate([
            'applicant_name' => 'required|string|min:2|max:100',
            'email' => 'required|email:rfc|max:150|unique:user_accounts,email',
            'phone' => ['required', new BdPhone, 'unique:user_accounts,phone'],
            'nid_number' => ['required', new Nid],
            'date_of_birth' => 'required|date|before_or_equal:'.now()->subYears(18)->toDateString(),
            'education_level' => 'required|string|max:60',
            'experience_summary' => 'nullable|string|max:1000',
            'thana_id' => 'required|exists:thanas,thana_id',
            'area_id' => 'required|exists:service_areas,area_id',
            'cv' => 'nullable|file|mimes:pdf|max:2048',
        ], [
            'date_of_birth.before_or_equal' => 'আবেদনকারীর বয়স কমপক্ষে ১৮ বছর হতে হবে।',
            'email.unique' => 'এই ইমেইল দিয়ে ইতোমধ্যে একটি অ্যাকাউন্ট আছে।',
            'phone.unique' => 'এই মোবাইল নম্বর দিয়ে ইতোমধ্যে একটি অ্যাকাউন্ট আছে।',
        ]);

        $area = ServiceArea::with('manager')->find($data['area_id']);
        if ($area->status !== 'Active' || ! $area->manager) {
            return back()->withInput()->withErrors(['area_id' => 'এই সেবা এলাকায় বর্তমানে আবেদন গ্রহণ করা হচ্ছে না।']);
        }
        // BR-21: only one open application per NID
        if (StaffApplication::where('nid_number', $data['nid_number'])->whereIn('status', ['Submitted', 'UnderReview', 'Approved'])->exists()) {
            return back()->withInput()->withErrors(['nid_number' => 'এই জাতীয় পরিচয়পত্র নম্বর দিয়ে ইতোমধ্যে একটি চলমান আবেদন আছে।']);
        }

        $path = $request->file('cv') ? $request->file('cv')->store('cvs', 'local') : null;
        unset($data['cv']);
        $app = StaffApplication::create($data + ['cv_file_path' => $path, 'status' => 'Submitted', 'submitted_at' => now()]);

        Audit::log('STAFF_APPLY', 'StaffApplication', $app->application_id, null, ['area' => $app->area_id, 'nid_number' => $app->nid_number], 'Success', null);
        Notifier::to($area->manager->user_id, 'STAFF_APPLICATION', 'নতুন স্টাফ আবেদন', "{$app->applicant_name} আপনার এলাকায় স্টাফ হওয়ার জন্য আবেদন করেছেন।");
        UserAccount::whereHas('role', fn ($r) => $r->where('role_name', 'ADMIN'))->where('status', 'Active')->pluck('user_id')
            ->each(fn ($uid) => Notifier::to($uid, 'STAFF_APPLICATION', 'নতুন স্টাফ আবেদন', "{$app->applicant_name} — {$area->area_name}"));

        return redirect()->route('staff.apply')->with('success', 'আপনার আবেদন সফলভাবে জমা হয়েছে। আবেদন নম্বর: '.bn_digits($app->application_id).'। পর্যালোচনার পর আপনাকে জানানো হবে।');
    }
}
