<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AreaManager;
use App\Models\Role;
use App\Models\ServiceArea;
use App\Models\Staff;
use App\Models\UserAccount;
use App\Rules\BdPhone;
use App\Rules\Nid;
use App\Rules\StrongPassword;
use App\Services\Audit;
use App\Services\PasswordPolicy;
use App\Services\TeamService;
use App\Support\Phone;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = UserAccount::with('role');
        if ($request->filled('role')) {
            $q->whereHas('role', fn ($r) => $r->where('role_name', $request->role));
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $t = '%'.$request->q.'%';
            $q->where(fn ($w) => $w->where('full_name', 'like', $t)->orWhere('email', 'like', $t)->orWhere('phone', 'like', $t));
        }

        return view('admin.users', ['users' => $q->latest('user_id')->paginate(15)->withQueryString()]);
    }

    public function create(string $type)
    {
        abort_unless(in_array($type, ['admin', 'manager', 'staff'], true), 404);

        return view('admin.user-create', [
            'type' => $type,
            'freeAreas' => ServiceArea::whereNull('manager_id')->where('status', 'Active')->orderBy('area_name')->get(),
            'managers' => AreaManager::with('user', 'serviceArea')->where('status', 'Active')->get(),
        ]);
    }

    public function store(Request $request, string $type, TeamService $team)
    {
        abort_unless(in_array($type, ['admin', 'manager', 'staff'], true), 404);
        $request->merge([
            'phone' => Phone::normalize($request->input('phone')) ?? $request->input('phone'),
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);
        $rules = [
            'full_name' => 'required|string|min:2|max:100',
            'email' => 'required|email:rfc|max:150|unique:user_accounts,email',
            'phone' => ['required', new BdPhone, 'unique:user_accounts,phone'],
            'password' => ['required', 'confirmed', new StrongPassword],
        ];
        if ($type === 'manager') {
            $rules['nid_number'] = ['required', new Nid, 'unique:area_managers,nid_number'];
            $rules['area_id'] = 'nullable|exists:service_areas,area_id';
        }
        if ($type === 'staff') {
            $rules['manager_id'] = 'required|exists:area_managers,manager_id';
            $rules['reason'] = 'required|string|min:5|max:300';
        }
        $data = $request->validate($rules, ['reason.required' => 'সরাসরি স্টাফ তৈরির কারণ লিখতে হবে।']);
        $roleName = ['admin' => 'ADMIN', 'manager' => 'AREA_MANAGER', 'staff' => 'STAFF'][$type];

        try {
            $user = DB::transaction(function () use ($data, $type, $roleName, $team) {
                $user = UserAccount::create([
                    'role_id' => Role::where('role_name', $roleName)->value('role_id'),
                    'full_name' => $data['full_name'], 'email' => $data['email'], 'phone' => $data['phone'], 'password_hash' => 'pending', 'status' => 'Active',
                ]);
                PasswordPolicy::set($user, $data['password']);
                if ($type === 'manager') {
                    $m = AreaManager::create(['user_id' => $user->user_id, 'nid_number' => $data['nid_number'], 'appointed_at' => now(), 'status' => 'Active']);
                    if (! empty($data['area_id'])) {
                        $area = ServiceArea::lockForUpdate()->find($data['area_id']);
                        if ($area->manager_id) {
                            throw new DomainException('এই সেবা এলাকায় ইতোমধ্যে একজন ম্যানেজার আছেন।');
                        }
                        $area->update(['manager_id' => $m->manager_id]);
                    }
                }
                if ($type === 'staff') {
                    $mgr = AreaManager::findOrFail($data['manager_id']);
                    $team->addStaff($mgr, $user->user_id, null);
                }

                return $user;
            });
        } catch (DomainException $e) {
            return back()->withInput($request->except('password', 'password_confirmation'))->with('error', $e->getMessage());
        }

        Audit::log($type === 'staff' ? 'OVERRIDE_STAFF_CREATE' : 'USER_CREATE', 'UserAccount', $user->user_id, null, ['role' => $roleName, 'reason' => $data['reason'] ?? null]);

        return redirect()->route('admin.users')->with('success', 'অ্যাকাউন্ট সফলভাবে তৈরি হয়েছে।');
    }

    public function toggle(Request $request, UserAccount $user, TeamService $team)
    {
        $request->validate(['reason' => 'nullable|string|max:300']);
        if ($user->user_id === $request->user()->user_id) {
            return back()->with('error', 'আপনি নিজের অ্যাকাউন্ট নিষ্ক্রিয় করতে পারবেন না।');
        }
        $activate = $user->status !== 'Active';

        try {
            DB::transaction(function () use ($user, $activate, $team) {
                if ($activate) {
                    if ($user->hasRole('STAFF') && $user->staff) {
                        if ($user->staff->status === 'Removed') {
                            throw new DomainException('অপসারিত স্টাফ পুনরায় সক্রিয় করা যায় না।');
                        }
                        $team->reactivate($user->staff);
                    }
                    if ($user->hasRole('AREA_MANAGER') && $user->manager) {
                        $user->manager->update(['status' => 'Active']);
                    }
                    $user->update(['status' => 'Active', 'failed_login_count' => 0, 'locked_until' => null]);
                } else {
                    if ($user->hasRole('STAFF') && $user->staff) {
                        $open = $user->staff->donations()->whereIn('status', ['Assigned', 'Verified', 'Approved', 'Collected'])->count();
                        if ($open > 0) {
                            throw new DomainException('এই স্টাফের নামে চলমান দান আছে; আগে সেগুলো শেষ হতে হবে।');
                        }
                        $user->staff->update(['status' => 'Suspended']);
                    }
                    if ($user->hasRole('AREA_MANAGER') && $user->manager) {
                        $user->manager->update(['status' => 'Inactive']);
                    }
                    $user->update(['status' => 'Inactive']);
                }
            });
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
        Audit::log($activate ? 'USER_ACTIVATE' : 'USER_DEACTIVATE', 'UserAccount', $user->user_id, null, ['reason' => $request->input('reason')]);

        return back()->with('success', $activate ? 'অ্যাকাউন্ট সক্রিয় করা হয়েছে।' : 'অ্যাকাউন্ট নিষ্ক্রিয় করা হয়েছে (ডেটা সংরক্ষিত থাকবে)।');
    }

    public function unlock(UserAccount $user)
    {
        $user->update(['status' => $user->status === 'Locked' ? 'Active' : $user->status, 'failed_login_count' => 0, 'locked_until' => null]);
        Audit::log('USER_UNLOCK', 'UserAccount', $user->user_id);

        return back()->with('success', 'অ্যাকাউন্ট আনলক করা হয়েছে।');
    }
}
