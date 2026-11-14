<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;

/** BR-24: a user has exactly one role and roles cannot be changed, so this page is read-only. */
class RoleController extends Controller
{
    public function index()
    {
        return view('admin.roles', ['roles' => Role::withCount([])->get()->each(function ($r) {
            $r->users_count = \App\Models\UserAccount::where('role_id', $r->role_id)->count();
            $r->active_count = \App\Models\UserAccount::where('role_id', $r->role_id)->where('status', 'Active')->count();
        })]);
    }
}
