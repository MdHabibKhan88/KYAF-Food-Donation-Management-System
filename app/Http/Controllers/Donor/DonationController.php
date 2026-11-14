<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Rules\BdPhone;
use App\Services\Audit;
use App\Services\DonationService;
use App\Support\Phone;
use DomainException;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        $donor = $request->user()->donor;
        $q = Donation::with('area')->where('donor_id', $donor->donor_id);
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $term = '%'.$request->q.'%';
            $q->where(fn ($w) => $w->where('donation_code', 'like', $term)->orWhere('food_name', 'like', $term));
        }
        if ($request->filled('from')) {
            $q->whereDate('submitted_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('submitted_at', '<=', $request->to);
        }

        return view('donor.donations', ['donations' => $q->latest('donation_id')->paginate(10)->withQueryString()]);
    }

    public function create(Request $request)
    {
        return view('donor.donate', ['phone' => $request->user()->phone, 'address' => $request->user()->donor->address_line]);
    }

    public function store(Request $request, DonationService $service)
    {
        $request->merge(['contact_phone' => Phone::normalize($request->input('contact_phone')) ?? $request->input('contact_phone')]);
        $data = $request->validate([
            'food_name' => 'required|string|min:2|max:100',
            'food_category' => 'required|in:Cooked,Packaged,Raw,Bakery',
            'quantity_meals' => 'required|integer|min:1|max:'.(int) setting('MAX_MEALS', 100000),
            'expiry_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'expiry_time' => 'required|date_format:H:i',
            'description' => 'nullable|string|max:500',
            'pickup_thana_id' => 'required|exists:thanas,thana_id',
            'pickup_address' => 'required|string|min:5|max:250',
            'contact_phone' => ['required', new BdPhone],
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ], [
            'quantity_meals.min' => 'খাবারের পরিমাণ কমপক্ষে ১ হতে হবে।',
            'quantity_meals.max' => 'খাবারের পরিমাণ খুব বেশি; সর্বোচ্চ '.bn_digits((int) setting('MAX_MEALS', 100000)).' মিল।',
            'quantity_meals.integer' => 'খাবারের পরিমাণ অবশ্যই পূর্ণ সংখ্যা হতে হবে (কত জন মানুষের খাবার)।',
            'expiry_date.after_or_equal' => 'মেয়াদ উত্তীর্ণের তারিখ আজকের বা ভবিষ্যতের হতে হবে।',
            'photo.image' => 'ছবিটি JPG বা PNG ফরম্যাটে হতে হবে।',
            'photo.mimes' => 'ছবিটি JPG বা PNG ফরম্যাটে হতে হবে।',
            'photo.max' => 'ছবির আকার ৫ MB এর বেশি হতে পারবে না।',
            'pickup_thana_id.required' => 'পিকআপ এলাকা (থানা) নির্বাচন করুন।',
        ]);

        try {
            $result = $service->submit($request->user()->donor, $data, $request->file('photo'), $request->boolean('confirm_duplicate'));
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if ($result === 'duplicate') {
            return back()->withInput()->with('duplicate', true);
        }

        return redirect()->route('donor.donations.show', $result)
            ->with('success', "আপনার দান সফলভাবে জমা হয়েছে! ট্র্যাকিং কোড: {$result->donation_code}। অনুগ্রহ করে কোডটি সংরক্ষণ করুন।");
    }

    public function show(Request $request, Donation $donation)
    {
        $this->own($request, $donation);
        $donation->load(['area', 'pickupThana.upazila.district', 'histories.changer', 'verifications', 'collection', 'distributions' => fn ($q) => $q->where('status', 'Completed')]);

        return view('donor.donation-show', ['donation' => $donation]);
    }

    public function cancel(Request $request, Donation $donation, DonationService $service)
    {
        $this->own($request, $donation);
        $data = $request->validate(['reason' => 'required|string|min:3|max:500'], ['reason.required' => 'বাতিলের কারণ লিখুন।']);
        try {
            $service->cancel($donation, $request->user(), $data['reason']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'দানটি বাতিল করা হয়েছে।');
    }

    /** BR-09: a donor sees only their own donations; anything else is 403 and logged. */
    private function own(Request $request, Donation $donation): void
    {
        if ($donation->donor_id !== $request->user()->donor->donor_id) {
            Audit::log('ACCESS_DENIED', 'Donation', $donation->donation_id, null, ['role' => 'DONOR'], 'Failure');
            abort(403);
        }
    }
}
