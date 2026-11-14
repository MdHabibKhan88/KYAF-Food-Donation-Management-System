<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ContactMessage;
use App\Models\Donation;
use App\Models\ServiceArea;
use App\Models\StaffApplication;
use App\Models\UserAccount;
use App\Services\TeamService;

class DashboardController extends Controller
{
    public function index()
    {
        $roleCount = fn (string $r) => UserAccount::whereHas('role', fn ($q) => $q->where('role_name', $r))->count();
        $areas = ServiceArea::with('manager.user')->where('status', 'Active')->get();

        return view('admin.dashboard', [
            'c' => [
                'users' => UserAccount::count(), 'donors' => $roleCount('DONOR'), 'managers' => $roleCount('AREA_MANAGER'), 'staff' => $roleCount('STAFF'),
                'donations' => Donation::count(),
                'pending' => Donation::whereIn('status', ['Submitted', 'Assigned', 'Verified', 'Approved'])->count(),
                'completed' => Donation::where('status', 'Completed')->count(),
                'areas' => ServiceArea::count(),
                'apps' => StaffApplication::whereIn('status', ['Submitted', 'UnderReview'])->count(),
                'messages' => ContactMessage::where('status', 'New')->count(),
            ],
            'understaffed' => $areas->filter(fn ($a) => $a->manager && $a->manager->activeStaffCount() < TeamService::capacity()),
            'noManager' => $areas->filter(fn ($a) => ! $a->manager),
            'byStatus' => Donation::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
            'logs' => AuditLog::with('user')->latest('log_id')->limit(8)->get(),
        ]);
    }
}
