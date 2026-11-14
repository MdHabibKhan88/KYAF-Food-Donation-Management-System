<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Staff;
use App\Services\Audit;
use App\Services\TeamService;
use DomainException;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request, TeamService $team)
    {
        $manager = $request->user()->manager;
        $staff = $manager->staff()->with('user')->withCount(['donations as open_count' => fn ($q) => $q->whereNotIn('status', Donation::FINAL)])->get();

        return view('manager.team', [
            'staff' => $staff, 'active' => $manager->activeStaffCount(), 'cap' => TeamService::capacity(),
            'vacancies' => max(0, TeamService::capacity() - $team->reservedSeats($manager)),
        ]);
    }

    public function update(Request $request, Staff $staff, TeamService $team)
    {
        if ($staff->manager_id !== $request->user()->manager->manager_id) {
            Audit::log('ACCESS_DENIED', 'Staff', $staff->staff_id, null, ['role' => 'AREA_MANAGER'], 'Failure');
            abort(403);
        }
        $data = $request->validate(['action' => 'required|in:suspend,remove,reactivate', 'reason' => 'required_unless:action,reactivate|nullable|string|max:300'],
            ['reason.required_unless' => 'কারণ লিখতে হবে।']);

        try {
            if ($data['action'] === 'reactivate') {
                if ($staff->status === 'Removed') {
                    throw new DomainException('অপসারিত স্টাফকে পুনরায় সক্রিয় করা যায় না; নতুন আবেদনের মাধ্যমে নিয়োগ দিন।');
                }
                $team->reactivate($staff);
                $staff->user->update(['status' => 'Active']);
            } else {
                $open = $staff->donations()->whereIn('status', ['Assigned', 'Verified', 'Approved', 'Collected'])->count();
                if ($open > 0) {
                    throw new DomainException('এই স্টাফের নামে '.bn_digits($open).'টি চলমান দান আছে; সেগুলো শেষ না হওয়া পর্যন্ত স্থগিত বা অপসারণ করা যাবে না।');
                }
                $staff->update(['status' => $data['action'] === 'suspend' ? 'Suspended' : 'Removed']);
                $staff->user->update(['status' => $data['action'] === 'suspend' ? 'Suspended' : 'Inactive']);
            }
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        Audit::log('STAFF_'.strtoupper($data['action']), 'Staff', $staff->staff_id, null, ['reason' => $data['reason'] ?? null]);

        return back()->with('success', 'স্টাফের অবস্থা হালনাগাদ হয়েছে।');
    }
}
