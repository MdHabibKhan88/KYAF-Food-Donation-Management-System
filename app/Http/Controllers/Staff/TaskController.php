<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\Donation;
use App\Rules\BdPhone;
use App\Services\Audit;
use App\Services\DonationService;
use DomainException;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $staff = $request->user()->staff;
        $q = Donation::with('pickupThana')->where('assigned_staff_id', $staff->staff_id);
        $history = $request->routeIs('staff.history');
        $history ? $q->whereIn('status', Donation::FINAL) : $q->whereNotIn('status', Donation::FINAL);
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q->where('donation_code', 'like', '%'.$request->q.'%');
        }

        return view('staff.tasks', ['donations' => $q->latest('donation_id')->paginate(10)->withQueryString(), 'history' => $history]);
    }

    public function show(Request $request, Donation $donation)
    {
        $this->mine($request, $donation);
        $donation->load(['area', 'pickupThana.upazila.district', 'donor.user', 'histories.changer', 'verifications', 'collection', 'distributions']);

        return view('staff.task-show', ['donation' => $donation]);
    }

    public function verify(Request $request, Donation $donation, DonationService $service)
    {
        $this->mine($request, $donation);
        $data = $request->validate([
            'result' => 'required|in:Verified,Rejected',
            'food_condition' => 'required|in:Good,Acceptable,Unsafe',
            'verified_quantity_meals' => 'required_if:result,Verified|nullable|integer|min:0|max:100000',
            'remarks' => 'required_if:result,Rejected|nullable|string|max:500',
        ], ['remarks.required_if' => 'প্রত্যাখ্যানের কারণ লিখতে হবে।']);

        return $this->run(fn () => $service->verify($donation, $request->user(), $data['result'], $data['food_condition'], (int) ($data['verified_quantity_meals'] ?? 0), $data['remarks'] ?? null),
            $data['result'] === 'Verified' ? 'দানটি সফলভাবে যাচাই করা হয়েছে।' : 'দানটি প্রত্যাখ্যান করা হয়েছে।');
    }

    public function collect(Request $request, Donation $donation, DonationService $service)
    {
        $this->mine($request, $donation);
        $data = $request->validate([
            'outcome' => 'required|in:collected,failed',
            'collected_quantity_meals' => 'required_if:outcome,collected|nullable|integer|min:1',
            'remarks' => 'required_if:outcome,failed|nullable|string|max:500',
        ], ['remarks.required_if' => 'সংগ্রহ ব্যর্থ হলে কারণ লিখতে হবে।']);

        return $this->run(fn () => $service->collect($donation, $request->user(), (int) ($data['collected_quantity_meals'] ?? 0), $data['remarks'] ?? null, $data['outcome'] === 'failed'),
            $data['outcome'] === 'collected' ? 'খাবার সংগ্রহ সম্পন্ন হয়েছে।' : 'ব্যর্থ সংগ্রহ নথিভুক্ত হয়েছে।');
    }

    public function planDistribution(Request $request, Donation $donation, DonationService $service)
    {
        $this->mine($request, $donation);
        $data = $request->validate([
            'point_name' => 'required|string|max:100',
            'point_address' => 'required|string|max:250',
            'thana_id' => 'required|exists:thanas,thana_id',
            'scheduled_start' => 'required|date_format:Y-m-d\TH:i',
            'scheduled_end' => 'required|date_format:Y-m-d\TH:i|after:scheduled_start',
            'meals_planned' => 'required|integer|min:1',
            'is_public' => 'nullable|boolean',
            'remarks' => 'nullable|string|max:500',
        ]);

        return $this->run(fn () => $service->planDistribution($donation, $request->user(), $data), 'বিতরণ ব্যাচ পরিকল্পনা করা হয়েছে।');
    }

    public function completeBatch(Request $request, Distribution $distribution, DonationService $service)
    {
        $this->mine($request, $distribution->donation);
        $data = $request->validate(['meals_distributed' => 'required|integer|min:0', 'remarks' => 'nullable|string|max:500']);

        return $this->run(fn () => $service->completeBatch($distribution, $request->user(), (int) $data['meals_distributed'], $data['remarks'] ?? null), 'বিতরণ ব্যাচ সম্পন্ন হয়েছে।');
    }

    public function cancelBatch(Request $request, Distribution $distribution, DonationService $service)
    {
        $this->mine($request, $distribution->donation);

        return $this->run(fn () => $service->cancelBatch($distribution, $request->user()), 'ব্যাচটি বাতিল করা হয়েছে।');
    }

    public function finish(Request $request, Donation $donation, DonationService $service)
    {
        $this->mine($request, $donation);

        return $this->run(fn () => $service->finishDistribution($donation, $request->user()), 'বিতরণ সমাপ্ত হিসেবে চিহ্নিত হয়েছে।');
    }

    /** BR-14: only the assigned staff member may act on a donation. */
    private function mine(Request $request, Donation $donation): void
    {
        $staff = $request->user()->staff;
        if (! $staff || $donation->assigned_staff_id !== $staff->staff_id) {
            Audit::log('ACCESS_DENIED', 'Donation', $donation->donation_id, null, ['role' => 'STAFF'], 'Failure');
            abort(403);
        }
    }

    private function run(callable $fn, string $okMessage)
    {
        try {
            $fn();
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', $okMessage);
    }
}
