<?php

namespace App\Http\Controllers\Donor;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $donor = $request->user()->donor()->with('thana.upazila.district')->firstOrFail();
        $base = Donation::where('donor_id', $donor->donor_id);

        return view('donor.dashboard', [
            'donor' => $donor,
            'counts' => (clone $base)->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
            'totalMeals' => (int) (clone $base)->whereIn('status', ['Collected', 'Distributed', 'Completed'])->sum('quantity_meals'),
            'recent' => (clone $base)->with('area')->latest('donation_id')->limit(5)->get(),
            'notifications' => UserNotification::where('user_id', $request->user()->user_id)->where('channel', 'InApp')->latest('notification_id')->limit(5)->get(),
        ]);
    }
}
