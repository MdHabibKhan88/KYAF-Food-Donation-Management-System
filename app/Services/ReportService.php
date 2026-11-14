<?php

namespace App\Services;

use App\Models\Distribution;
use App\Models\Donation;
use App\Models\ServiceArea;
use App\Models\UserAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/** BR-30: Admin sees everything, Area Manager own area, Staff and Donor own data only. */
class ReportService
{
    public function scope(UserAccount $user): Builder
    {
        $q = Donation::query();
        switch ($user->roleName()) {
            case 'ADMIN':
                break;
            case 'AREA_MANAGER':
                $q->where('donations.area_id', optional(optional($user->manager)->serviceArea)->area_id ?? 0);
                break;
            case 'STAFF':
                $q->where('donations.assigned_staff_id', optional($user->staff)->staff_id ?? 0);
                break;
            default:
                $q->where('donations.donor_id', optional($user->donor)->donor_id ?? '');
        }

        return $q;
    }

    public function filtered(UserAccount $user, array $f): Builder
    {
        $q = $this->scope($user);
        if (! empty($f['status'])) {
            $q->where('donations.status', $f['status']);
        }
        if (! empty($f['area_id']) && $user->hasRole('ADMIN')) {
            $q->where('donations.area_id', (int) $f['area_id']);
        }
        if (! empty($f['from'])) {
            $q->whereDate('donations.submitted_at', '>=', $f['from']);
        }
        if (! empty($f['to'])) {
            $q->whereDate('donations.submitted_at', '<=', $f['to']);
        }
        if (! empty($f['q'])) {
            $t = '%'.$f['q'].'%';
            $q->where(fn ($w) => $w->where('donations.donation_code', 'like', $t)->orWhere('donations.food_name', 'like', $t));
        }

        return $q;
    }

    public function build(UserAccount $user, array $f): array
    {
        $base = fn () => $this->filtered($user, $f);
        $ids = fn () => $base()->select('donation_id');

        $byStatus = $base()->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $r = [
            'total' => (int) $byStatus->sum(),
            'by_status' => $byStatus,
            'meals_donated' => (int) $base()->sum('quantity_meals'),
            'meals_collected' => (int) DB::table('collections')->whereIn('donation_id', $ids())->where('status', 'Collected')->sum('collected_quantity_meals'),
            'meals_distributed' => (int) Distribution::whereIn('donation_id', $ids())->where('status', 'Completed')->sum('meals_distributed'),
        ];

        $month = DB::getDriverName() === 'sqlite' ? "strftime('%Y-%m', submitted_at)" : "DATE_FORMAT(submitted_at, '%Y-%m')";
        $r['monthly'] = $base()->selectRaw("$month as m, count(*) c, sum(quantity_meals) meals")->groupBy('m')->orderBy('m')->get();

        if ($user->hasRole('ADMIN')) {
            $r['by_area'] = $base()->join('service_areas', 'service_areas.area_id', '=', 'donations.area_id')
                ->selectRaw('service_areas.area_name as name, count(*) c, sum(donations.quantity_meals) meals')
                ->groupBy('service_areas.area_id', 'service_areas.area_name')->orderByDesc('c')->get();
        }

        if (in_array($user->roleName(), ['ADMIN', 'AREA_MANAGER'], true)) {
            $r['staff_perf'] = $base()->join('staff', 'staff.staff_id', '=', 'donations.assigned_staff_id')
                ->join('user_accounts', 'user_accounts.user_id', '=', 'staff.user_id')
                ->selectRaw("user_accounts.full_name as name, count(*) assigned, sum(case when donations.status in ('Collected','Distributed','Completed') then 1 else 0 end) collected, sum(case when donations.status in ('Distributed','Completed') then 1 else 0 end) distributed")
                ->groupBy('staff.staff_id', 'user_accounts.full_name')->orderByDesc('assigned')->get();
        }

        return $r;
    }

    public function areasForFilter(UserAccount $user)
    {
        return $user->hasRole('ADMIN') ? ServiceArea::orderBy('area_name')->get(['area_id', 'area_name']) : collect();
    }
}
