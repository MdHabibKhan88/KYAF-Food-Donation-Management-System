<?php

namespace App\Http\Middleware;

use App\Services\Audit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** BR-10: only the authorised role may open a dashboard; anything else is 403 and audit-logged. */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }
        if ($user->status !== 'Active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'আপনার অ্যাকাউন্টটি সক্রিয় নয়। অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন।');
        }
        if (! in_array($user->roleName(), $roles, true)) {
            Audit::log('ACCESS_DENIED', 'Route', $request->path(), null, ['role' => $user->roleName(), 'method' => $request->method()], 'Failure');
            abort(403);
        }

        return $next($request);
    }
}
