<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Thana;
use App\Models\Upazila;
use Illuminate\Http\Request;

/** JSON endpoints for the dependent Division > District > Upazila > Thana selectors. */
class LocationController extends Controller
{
    public function divisions()
    {
        return response()->json(District::query()->select('division_name')->distinct()->orderBy('division_name')->pluck('division_name'));
    }

    public function districts(Request $request)
    {
        return response()->json(
            District::where('division_name', (string) $request->query('division'))->orderBy('name_bn')->get(['district_id as id', 'name_bn as name'])
        );
    }

    public function upazilas(Request $request)
    {
        return response()->json(
            Upazila::where('district_id', (int) $request->query('district_id'))->orderBy('name_bn')->get(['upazila_id as id', 'name_bn as name'])
        );
    }

    public function thanas(Request $request)
    {
        return response()->json(
            Thana::where('upazila_id', (int) $request->query('upazila_id'))->orderBy('name_bn')
                ->get(['thana_id as id', 'name_bn as name', 'area_id'])
                ->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'covered' => $t->area_id !== null])
        );
    }

    /** Resolve a thana (or upazila / district) id back to its chain, used to pre-fill the selectors. */
    public function chain(Request $request)
    {
        if ($request->filled('thana_id')) {
            $t = Thana::with('upazila.district')->find((int) $request->query('thana_id'));
            if ($t) {
                return response()->json([
                    'division' => $t->upazila->district->division_name, 'district_id' => $t->upazila->district_id,
                    'upazila_id' => $t->upazila_id, 'thana_id' => $t->thana_id, 'label' => $t->fullName(), 'covered' => $t->area_id !== null,
                ]);
            }
        }
        if ($request->filled('upazila_id')) {
            $u = Upazila::with('district')->find((int) $request->query('upazila_id'));
            if ($u) {
                return response()->json(['division' => $u->district->division_name, 'district_id' => $u->district_id, 'upazila_id' => $u->upazila_id, 'thana_id' => null]);
            }
        }
        if ($request->filled('district_id')) {
            $d = District::find((int) $request->query('district_id'));
            if ($d) {
                return response()->json(['division' => $d->division_name, 'district_id' => $d->district_id, 'upazila_id' => null, 'thana_id' => null]);
            }
        }

        return response()->json(null);
    }
}
