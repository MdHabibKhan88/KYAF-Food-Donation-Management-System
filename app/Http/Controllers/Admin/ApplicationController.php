<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceArea;
use App\Models\StaffApplication;
use App\Services\ApplicationService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $q = StaffApplication::with('area', 'thana');
        foreach (['status', 'area_id'] as $k) {
            if ($request->filled($k)) {
                $q->where($k, $request->input($k));
            }
        }
        if ($request->filled('q')) {
            $t = '%'.$request->q.'%';
            $q->where(fn ($w) => $w->where('applicant_name', 'like', $t)->orWhere('email', 'like', $t));
        }

        return view('admin.applications', ['apps' => $q->latest('application_id')->paginate(15)->withQueryString(), 'areas' => ServiceArea::orderBy('area_name')->get()]);
    }

    public function show(StaffApplication $application)
    {
        return view('admin.application-show', ['app' => $application->load('thana.upazila.district', 'area.manager.user', 'reviewer')]);
    }

    public function approve(Request $request, StaffApplication $application, ApplicationService $svc)
    {
        $d = $request->validate(['reason' => 'required|string|min:5|max:300']);
        try {
            $link = $svc->approve($application, $request->user(), $d['reason']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'ওভাররাইড: আবেদন অনুমোদিত। আমন্ত্রণ লিংকটি আবেদনকারীকে পাঠান।')->with('invite_link', $link);
    }

    public function reject(Request $request, StaffApplication $application, ApplicationService $svc)
    {
        $d = $request->validate(['remarks' => 'required|string|min:3|max:500', 'reason' => 'required|string|min:5|max:300']);
        try {
            $svc->reject($application, $request->user(), $d['remarks'], $d['reason']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'ওভাররাইড: আবেদন প্রত্যাখ্যাত।');
    }

    public function cv(StaffApplication $application)
    {
        abort_unless($application->cv_file_path && Storage::disk('local')->exists($application->cv_file_path), 404);

        return Storage::disk('local')->download($application->cv_file_path, 'cv-'.$application->application_id.'.pdf');
    }
}
