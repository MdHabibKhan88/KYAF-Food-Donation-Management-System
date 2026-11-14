<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\StaffApplication;
use App\Services\Audit;
use App\Services\ApplicationService;
use App\Services\TeamService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $area = $this->area($request);
        $q = StaffApplication::with('thana')->where('area_id', $area->area_id);
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $t = '%'.$request->q.'%';
            $q->where(fn ($w) => $w->where('applicant_name', 'like', $t)->orWhere('email', 'like', $t));
        }

        return view('manager.applications', ['apps' => $q->latest('application_id')->paginate(12)->withQueryString()]);
    }

    public function show(Request $request, StaffApplication $application, TeamService $team)
    {
        $this->check($request, $application);
        $application->load('thana.upazila.district', 'area', 'reviewer');

        return view('manager.application-show', ['app' => $application, 'vacancy' => $team->hasVacancy($request->user()->manager)]);
    }

    public function review(Request $request, StaffApplication $application, ApplicationService $svc)
    {
        $this->check($request, $application);
        try {
            $svc->markUnderReview($application, $request->user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'আবেদনটি পর্যালোচনাধীন হিসেবে চিহ্নিত হয়েছে।');
    }

    public function approve(Request $request, StaffApplication $application, ApplicationService $svc)
    {
        $this->check($request, $application);
        try {
            $link = $svc->approve($application, $request->user());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'আবেদন অনুমোদিত হয়েছে। নিচের আমন্ত্রণ লিংকটি আবেদনকারীকে পাঠান (৭২ ঘণ্টা বৈধ, একবার ব্যবহারযোগ্য)।')->with('invite_link', $link);
    }

    public function reject(Request $request, StaffApplication $application, ApplicationService $svc)
    {
        $this->check($request, $application);
        $data = $request->validate(['remarks' => 'required|string|min:3|max:500'], ['remarks.required' => 'প্রত্যাখ্যানের কারণ লিখতে হবে।']);
        try {
            $svc->reject($application, $request->user(), $data['remarks']);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'আবেদনটি প্রত্যাখ্যান করা হয়েছে।');
    }

    public function cv(Request $request, StaffApplication $application)
    {
        $this->check($request, $application);
        abort_unless($application->cv_file_path && Storage::disk('local')->exists($application->cv_file_path), 404);
        Audit::log('CV_DOWNLOAD', 'StaffApplication', $application->application_id);

        return Storage::disk('local')->download($application->cv_file_path, 'cv-'.$application->application_id.'.pdf');
    }

    private function area(Request $request)
    {
        $area = optional($request->user()->manager)->serviceArea;
        abort_unless($area, 403, 'আপনাকে এখনো কোনো সেবা এলাকায় নিয়োগ দেওয়া হয়নি।');

        return $area;
    }

    private function check(Request $request, StaffApplication $application): void
    {
        if ($application->area_id !== $this->area($request)->area_id) {
            Audit::log('ACCESS_DENIED', 'StaffApplication', $application->application_id, null, ['role' => 'AREA_MANAGER'], 'Failure');
            abort(403);
        }
    }
}
