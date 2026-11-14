<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Services\Audit;
use App\Services\DonationService;
use DomainException;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        $area = $this->area($request);
        $q = Donation::with('assignedStaff.user', 'pickupThana')->where('area_id', $area->area_id);
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $t = '%'.$request->q.'%';
            $q->where(fn ($w) => $w->where('donation_code', 'like', $t)->orWhere('food_name', 'like', $t));
        }
        if ($request->filled('from')) {
            $q->whereDate('submitted_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('submitted_at', '<=', $request->to);
        }

        return view('manager.donations', ['donations' => $q->latest('donation_id')->paginate(12)->withQueryString(), 'area' => $area]);
    }

    public function show(Request $request, Donation $donation)
    {
        $area = $this->area($request);
        $this->authorizeArea($area, $donation);
        $donation->load(['donor.user', 'pickupThana.upazila.district', 'assignedStaff.user', 'histories.changer', 'verifications.staff.user', 'approvals.decider', 'collection', 'distributions']);
        $team = $request->user()->manager->staff()->with('user')->where('status', 'Active')->get();

        return view('manager.donation-show', ['donation' => $donation, 'team' => $team]);
    }

    public function assign(Request $request, Donation $donation, DonationService $service)
    {
        $this->authorizeArea($this->area($request), $donation);
        $data = $request->validate(['staff_id' => 'required|integer']);

        return $this->run(fn () => $service->assign($donation, (int) $data['staff_id'], $request->user()), 'স্টাফ সফলভাবে নিয়োগ করা হয়েছে।');
    }

    public function decide(Request $request, Donation $donation, DonationService $service)
    {
        $this->authorizeArea($this->area($request), $donation);
        $data = $request->validate([
            'decision' => 'required|in:Approved,Rejected',
            'remarks' => 'required_if:decision,Rejected|nullable|string|max:500',
        ], ['remarks.required_if' => 'প্রত্যাখ্যানের কারণ লিখতে হবে।']);

        return $this->run(fn () => $service->decide($donation, $request->user(), $data['decision'], $data['remarks'] ?? null),
            $data['decision'] === 'Approved' ? 'দানটি অনুমোদন করা হয়েছে।' : 'দানটি প্রত্যাখ্যান করা হয়েছে।');
    }

    public function close(Request $request, Donation $donation, DonationService $service)
    {
        $this->authorizeArea($this->area($request), $donation);

        return $this->run(fn () => $service->close($donation, $request->user()), 'দানটি সম্পন্ন হিসেবে বন্ধ করা হয়েছে।');
    }

    private function area(Request $request)
    {
        $area = optional($request->user()->manager)->serviceArea;
        abort_unless($area, 403, 'আপনাকে এখনো কোনো সেবা এলাকায় নিয়োগ দেওয়া হয়নি।');

        return $area;
    }

    private function authorizeArea($area, Donation $donation): void
    {
        if ($donation->area_id !== $area->area_id) {
            Audit::log('ACCESS_DENIED', 'Donation', $donation->donation_id, null, ['role' => 'AREA_MANAGER'], 'Failure');
            abort(403);
        }
    }

    private function run(callable $fn, string $ok)
    {
        try {
            $fn();
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', $ok);
    }
}
