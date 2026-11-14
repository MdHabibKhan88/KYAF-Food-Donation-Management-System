<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $staff = $request->user()->staff()->with('manager.serviceArea.district', 'manager.user')->firstOrFail();
        $mine = Donation::where('assigned_staff_id', $staff->staff_id);

        return view('staff.dashboard', [
            'staff' => $staff,
            'pending' => (clone $mine)->with('pickupThana')->whereIn('status', ['Assigned', 'Approved', 'Collected'])->orderBy('expiry_date')->orderBy('expiry_time')->get(),
            'counts' => (clone $mine)->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
        ]);
    }
}
