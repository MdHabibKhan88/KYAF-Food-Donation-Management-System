<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AreaManager;
use App\Models\District;
use App\Models\ServiceArea;
use App\Models\Thana;
use App\Services\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** BR-02: one manager per area, one area per manager, every thana in at most one area. */
class ServiceAreaController extends Controller
{
    public function index(Request $request)
    {
        $q = ServiceArea::with('district', 'manager.user')->withCount('thanas');
        if ($request->filled('district_id')) {
            $q->where('district_id', (int) $request->district_id);
        }
        if ($request->filled('q')) {
            $t = '%'.$request->q.'%';
            $q->where(fn ($w) => $w->where('area_name', 'like', $t)->orWhere('area_code', 'like', $t));
        }

        return view('admin.areas', ['areas' => $q->orderBy('area_code')->paginate(15)->withQueryString(), 'districts' => District::orderBy('name_bn')->get()]);
    }

    public function create()
    {
        return view('admin.area-form', ['area' => new ServiceArea(['status' => 'Active']), 'districts' => District::orderBy('name_bn')->get(), 'managers' => $this->freeManagers(null), 'selected' => collect()]);
    }

    public function edit(ServiceArea $area)
    {
        $area->load('thanas.upazila.district');

        return view('admin.area-form', ['area' => $area, 'districts' => District::orderBy('name_bn')->get(), 'managers' => $this->freeManagers($area), 'selected' => $area->thanas]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);
        DB::transaction(function () use ($data) {
            $area = ServiceArea::create(collect($data)->except('thana_ids')->all());
            $this->syncThanas($area, $data['thana_ids'] ?? []);
            Audit::log('AREA_CREATE', 'ServiceArea', $area->area_id, null, collect($data)->except('thana_ids')->all() + ['thanas' => count($data['thana_ids'] ?? [])]);
        });

        return redirect()->route('admin.areas')->with('success', 'সেবা এলাকা তৈরি হয়েছে।');
    }

    public function update(Request $request, ServiceArea $area)
    {
        $data = $this->validated($request, $area);
        DB::transaction(function () use ($data, $area) {
            $old = $area->only(['area_code', 'area_name', 'district_id', 'manager_id', 'status']);
            $area->update(collect($data)->except('thana_ids')->all());
            $this->syncThanas($area, $data['thana_ids'] ?? []);
            Audit::log('AREA_UPDATE', 'ServiceArea', $area->area_id, $old, collect($data)->except('thana_ids')->all() + ['thanas' => count($data['thana_ids'] ?? [])]);
        });

        return redirect()->route('admin.areas')->with('success', 'সেবা এলাকা হালনাগাদ হয়েছে।');
    }

    private function validated(Request $request, ?ServiceArea $area): array
    {
        $request->merge(['area_code' => strtoupper(trim((string) $request->input('area_code')))]);
        $data = $request->validate([
            'area_code' => ['required', 'regex:/^[A-Z0-9\-]{2,12}$/', Rule::unique('service_areas', 'area_code')->ignore($area?->area_id, 'area_id')],
            'area_name' => 'required|string|min:2|max:100',
            'district_id' => 'required|exists:districts,district_id',
            'manager_id' => ['nullable', 'exists:area_managers,manager_id', Rule::unique('service_areas', 'manager_id')->ignore($area?->area_id, 'area_id')],
            'status' => 'required|in:Active,Inactive',
            'thana_ids' => 'nullable|array',
            'thana_ids.*' => 'integer|exists:thanas,thana_id',
        ], [
            'area_code.regex' => 'এলাকার কোড ২–১২ অক্ষরের ইংরেজি বড় হাতের অক্ষর, সংখ্যা বা হাইফেন হতে পারবে।',
            'manager_id.unique' => 'এই ম্যানেজার ইতোমধ্যে অন্য একটি সেবা এলাকার দায়িত্বে আছেন।',
        ]);
        $data['manager_id'] = ($data['manager_id'] ?? null) ?: null;
        if (empty($data['thana_ids']) && $request->input('status') === 'Active' && ! $area) {
            throw ValidationException::withMessages(['thana_ids' => 'অন্তত একটি থানা নির্বাচন করুন।']);
        }
        // BR-02: a thana may belong to only one area
        $taken = Thana::whereIn('thana_id', $data['thana_ids'] ?? [])->whereNotNull('area_id')->when($area, fn ($q) => $q->where('area_id', '!=', $area->area_id))->pluck('name_bn');
        if ($taken->isNotEmpty()) {
            throw ValidationException::withMessages(['thana_ids' => 'এই থানাগুলো ইতোমধ্যে অন্য সেবা এলাকায় আছে: '.$taken->implode(', ')]);
        }

        return $data;
    }

    private function syncThanas(ServiceArea $area, array $ids): void
    {
        Thana::where('area_id', $area->area_id)->whereNotIn('thana_id', $ids ?: [0])->update(['area_id' => null]);
        if ($ids) {
            Thana::whereIn('thana_id', $ids)->update(['area_id' => $area->area_id]);
        }
    }

    private function freeManagers(?ServiceArea $area)
    {
        return AreaManager::with('user')->where('status', 'Active')
            ->where(fn ($q) => $q->whereDoesntHave('serviceArea')->when($area, fn ($w) => $w->orWhere('manager_id', $area->manager_id)))->get();
    }
}
