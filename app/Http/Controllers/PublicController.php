<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Distribution;
use App\Models\Donation;
use App\Models\ServiceArea;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function home()
    {
        return view('public.home', [
            'stats' => [
                'donations' => Donation::where('status', 'Completed')->count(),
                'meals' => (int) Distribution::where('status', 'Completed')->sum('meals_distributed'),
                'areas' => ServiceArea::where('status', 'Active')->count(),
            ],
        ]);
    }

    public function about()
    {
        return view('public.about');
    }

    /** BR-29: only public batches; no donor or staff personal data is ever selected. */
    public function findFood(Request $request)
    {
        $q = Distribution::query()
            ->with('thana.upazila.district')
            ->where('is_public', true)
            ->whereIn('status', ['Planned', 'Ongoing'])
            ->where('scheduled_end', '>', now())
            ->select(['distribution_id', 'point_name', 'point_address', 'thana_id', 'scheduled_start', 'scheduled_end', 'meals_planned', 'meals_distributed', 'status']);

        if ($request->filled('thana_id')) {
            $q->where('thana_id', (int) $request->thana_id);
        } elseif ($request->filled('upazila_id')) {
            $q->whereHas('thana', fn ($t) => $t->where('upazila_id', (int) $request->upazila_id));
        } elseif ($request->filled('district_id')) {
            $q->whereHas('thana.upazila', fn ($u) => $u->where('district_id', (int) $request->district_id));
        }

        return view('public.find-food', [
            'batches' => $q->orderBy('scheduled_start')->paginate(12)->withQueryString(),
            'districts' => District::orderBy('name_bn')->get(),
        ]);
    }
}
