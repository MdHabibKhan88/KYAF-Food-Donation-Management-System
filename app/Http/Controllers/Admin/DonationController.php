<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\ServiceArea;
use App\Services\DonationService;
use DomainException;
use Illuminate\Http\Request;

/** Admin may override assignment, approval, closing and cancellation, always with a reason (BR-32). */
class DonationController extends Controller
{
    public function index(Request $request)
    {
        $q = Donation::with('area', 'assignedStaff.user');
        foreach (['status', 'area_id'] as $k) {
            if ($request->filled($k)) {
                $q->where($k, $request->input($k));
            }
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

        return view('admin.donations', ['donations' => $q->latest('donation_id')->paginate(15)->withQueryString(), 'areas' => ServiceArea::orderBy('area_name')->get()]);
    }

    public function show(Donation $donation)
    {
        $donation->load(['donor.user', 'area.manager.user', 'pickupThana.upazila.district', 'assignedStaff.user', 'histories.changer', 'verifications.staff.user', 'approvals.decider', 'collection', 'distributions']);
        $team = optional($donation->area->manager)->staff()?->with('user')->where('status', 'Active')->get() ?? collect();

        return view('admin.donation-show', ['donation' => $donation, 'team' => $team]);
    }

    public function assign(Request $request, Donation $donation, DonationService $s)
    {
        $d = $request->validate(['staff_id' => 'required|integer', 'reason' => 'required|string|min:5|max:300']);

        return $this->run(fn () => $s->assign($donation, (int) $d['staff_id'], $request->user(), $d['reason']), 'ওভাররাইড: স্টাফ নিয়োগ করা হয়েছে।');
    }

    public function decide(Request $request, Donation $donation, DonationService $s)
    {
        $d = $request->validate(['decision' => 'required|in:Approved,Rejected', 'reason' => 'required|string|min:5|max:300', 'remarks' => 'required_if:decision,Rejected|nullable|string|max:500']);

        return $this->run(fn () => $s->decide($donation, $request->user(), $d['decision'], $d['remarks'] ?? null, $d['reason']), 'ওভাররাইড: সিদ্ধান্ত নথিভুক্ত হয়েছে।');
    }

    public function close(Request $request, Donation $donation, DonationService $s)
    {
        $d = $request->validate(['reason' => 'required|string|min:5|max:300']);

        return $this->run(fn () => $s->close($donation, $request->user(), $d['reason']), 'ওভাররাইড: দান সম্পন্ন করা হয়েছে।');
    }

    public function cancel(Request $request, Donation $donation, DonationService $s)
    {
        $d = $request->validate(['reason' => 'required|string|min:5|max:300']);

        return $this->run(fn () => $s->cancel($donation, $request->user(), $d['reason']), 'ওভাররাইড: দান বাতিল করা হয়েছে।');
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
