<?php

namespace App\Http\Controllers;

use App\Rules\BdPhone;
use App\Services\Audit;
use App\Support\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user()->load('role', 'donor.thana.upazila.district', 'manager.serviceArea', 'staff.manager.serviceArea');

        return view('shared.profile', ['user' => $user]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $request->merge([
            'phone' => Phone::normalize($request->input('phone')) ?? $request->input('phone'),
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
        $rules = [
            'full_name' => 'required|string|min:2|max:100',
            'email' => ['required', 'email:rfc', 'max:150', Rule::unique('user_accounts', 'email')->ignore($user->user_id, 'user_id')],
            'phone' => ['required', new BdPhone, Rule::unique('user_accounts', 'phone')->ignore($user->user_id, 'user_id')],
        ];
        if ($user->donor) {
            $rules += ['address_line' => 'required|string|max:250', 'thana_id' => 'required|exists:thanas,thana_id', 'organization_name' => 'nullable|string|max:120'];
        }
        $data = $request->validate($rules);

        $old = $user->only(['full_name', 'email', 'phone']);
        $user->update(['full_name' => $data['full_name'], 'email' => $data['email'], 'phone' => $data['phone']]);
        if ($user->donor) {
            $user->donor->update([
                'address_line' => $data['address_line'], 'thana_id' => $data['thana_id'],
                'organization_name' => $user->donor->donor_type === 'Individual' ? null : ($data['organization_name'] ?? $user->donor->organization_name),
            ]);
        }
        Audit::log('PROFILE_UPDATE', 'UserAccount', $user->user_id, $old, ['full_name' => $data['full_name'], 'email' => $data['email'], 'phone' => $data['phone']]);

        return back()->with('success', 'প্রোফাইল সফলভাবে হালনাগাদ হয়েছে।');
    }
    public function photo(Request $request)
  {
    $path = $request->user()->profile_photo_path;
    abort_unless($path && Storage::disk('local')->exists($path), 404);

    return Storage::disk('local')->response($path);
  }

  public function updatePhoto(Request $request)
  {
    $request->validate([
        'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
    ], [
        'photo.required' => 'একটি ছবি নির্বাচন করুন।',
        'photo.image' => 'ফাইলটি অবশ্যই ছবি হতে হবে।',
        'photo.mimes' => 'ছবিটি JPG বা PNG ফরম্যাটে হতে হবে।',
        'photo.max' => 'ছবির আকার ২ MB এর বেশি হতে পারবে না।',
    ]);

    $user = $request->user();
    $old = $user->profile_photo_path;
    $path = $request->file('photo')->store('avatars', 'local');
    $user->update(['profile_photo_path' => $path]);
    if ($old && Storage::disk('local')->exists($old)) {
        Storage::disk('local')->delete($old);
    }
    Audit::log('PROFILE_PHOTO_UPDATE', 'UserAccount', $user->user_id);

    return back()->with('success', 'প্রোফাইল ছবি সফলভাবে হালনাগাদ হয়েছে।');
   }

    public function deletePhoto(Request $request)
  {
    $user = $request->user();
    if ($user->profile_photo_path) {
        Storage::disk('local')->delete($user->profile_photo_path);
        $user->update(['profile_photo_path' => null]);
        Audit::log('PROFILE_PHOTO_DELETE', 'UserAccount', $user->user_id);
    }

    return back()->with('success', 'প্রোফাইল ছবি মুছে ফেলা হয়েছে।');
   }
}
