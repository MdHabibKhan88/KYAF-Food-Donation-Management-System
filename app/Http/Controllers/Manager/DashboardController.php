<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\StaffApplication;
use App\Services\TeamService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, TeamService $team)
    {
        $manager = $request->user()->manager()->with('serviceArea.district', 'serviceArea.thanas.upazila')->firstOrFail();
        $area = $manager->serviceArea;
        $active = $manager->activeStaffCount();
        $cap = TeamService::capacity();

        $counts = collect();
        $recent = collect();
        $pendingApps = 0;
        if ($area) {
            $counts = Donation::where('area_id', $area->area_id)->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
            $recent = Donation::with('assignedStaff.user')->where('area_id', $area->area_id)->latest('donation_id')->limit(6)->get();
            $pendingApps = StaffApplication::where('area_id', $area->area_id)->whereIn('status', ['Submitted', 'UnderReview'])->count();
        }

        return view('manager.dashboard', [
            'manager' => $manager, 'area' => $area, 'active' => $active, 'cap' => $cap,
            'vacancies' => max(0, $cap - $team->reservedSeats($manager)), 'understaffed' => $active < $cap,
            'counts' => $counts, 'recent' => $recent, 'pendingApps' => $pendingApps,
        ]);
    }
}
