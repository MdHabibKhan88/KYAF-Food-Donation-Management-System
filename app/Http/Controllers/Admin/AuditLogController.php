<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $q = AuditLog::with('user');
        foreach (['action_type', 'entity_name', 'result'] as $k) {
            if ($request->filled($k)) {
                $q->where($k, 'like', $request->input($k).($k === 'result' ? '' : '%'));
            }
        }
        if ($request->filled('user')) {
            $t = '%'.$request->user.'%';
            $q->whereHas('user', fn ($u) => $u->where('full_name', 'like', $t)->orWhere('email', 'like', $t));
        }
        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->to);
        }

        return view('admin.audit', [
            'logs' => $q->latest('log_id')->paginate(25)->withQueryString(),
            'actions' => AuditLog::select('action_type')->distinct()->orderBy('action_type')->pluck('action_type'),
        ]);
    }
}
