<?php

namespace App\Http\Controllers;

use App\Services\Audit;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $svc)
    {
        $f = $request->validate([
            'from' => 'nullable|date', 'to' => 'nullable|date|after_or_equal:from', 'status' => 'nullable|string|max:20',
            'area_id' => 'nullable|integer', 'q' => 'nullable|string|max:100',
        ]);
        $user = $request->user();
        $rows = $svc->filtered($user, $f)->with('area')->latest('donation_id')->paginate(15)->withQueryString();

        return view('shared.reports', [
            'r' => $svc->build($user, $f), 'rows' => $rows, 'areas' => $svc->areasForFilter($user), 'f' => $f,
        ]);
    }

    /** CSV export. Personal data is masked for Staff and Area Managers (BR-30); Donors/Staff export only their own rows. */
    public function export(Request $request, ReportService $svc)
    {
        $f = $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date', 'status' => 'nullable|string|max:20', 'area_id' => 'nullable|integer', 'q' => 'nullable|string|max:100']);
        $user = $request->user();
        $isAdmin = $user->hasRole('ADMIN');
        Audit::log('REPORT_EXPORT', 'Report', null, null, $f);

        $q = $svc->filtered($user, $f)->with('area', 'donor.user');

        return response()->streamDownload(function () use ($q, $isAdmin) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Bangla correctly
            $head = ['ট্র্যাকিং কোড', 'খাবার', 'ধরন', 'মিল', 'অবস্থা', 'সেবা এলাকা', 'জমার তারিখ'];
            if ($isAdmin) {
                $head = array_merge($head, ['দাতা আইডি', 'দাতার নাম']);
            }
            fputcsv($out, $head);
            $q->orderBy('donation_id')->chunk(500, function ($chunk) use ($out, $isAdmin) {
                foreach ($chunk as $d) {
                    $row = [$d->donation_code, $d->food_name, __('kyaf.category.'.$d->food_category), $d->quantity_meals, $d->statusLabel(), optional($d->area)->area_name, bst($d->submitted_at)];
                    if ($isAdmin) {
                        $row = array_merge($row, [$d->donor_id, optional(optional($d->donor)->user)->full_name]);
                    }
                    fputcsv($out, $row);
                }
            });
            fclose($out);
        }, 'kyaf-report-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
