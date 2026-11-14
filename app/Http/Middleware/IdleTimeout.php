<?php

namespace App\Http\Middleware;

use App\Services\Audit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/** BR-33: a session ends after 30 minutes (configurable) of inactivity. */
class IdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $limit = max(1, (int) setting('SESSION_TIMEOUT_MIN', 30)) * 60;
            $last = $request->session()->get('last_activity');
            if ($last && (now()->timestamp - $last) > $limit) {
                Audit::log('SESSION_TIMEOUT', 'UserAccount', Auth::id());
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'নিষ্ক্রিয়তার কারণে আপনার সেশন শেষ হয়েছে। আবার লগইন করুন।');
            }
            $request->session()->put('last_activity', now()->timestamp);
        }

        return $next($request);
    }
}
