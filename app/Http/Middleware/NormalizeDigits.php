<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Converts Bangla digits (০-৯) to ASCII for numeric/phone fields so validation works with a Bangla keyboard. Passwords are never touched. */
class NormalizeDigits
{
    private const KEYS = ['phone', 'contact_phone', 'nid_number', 'quantity_meals', 'verified_quantity_meals', 'collected_quantity_meals',
        'meals_planned', 'meals_distributed', 'captcha', 'area_code', 'setting_value', 'LOGIN_MAX_ATTEMPTS', 'LOGIN_LOCK_MINUTES',
        'SESSION_TIMEOUT_MIN', 'MIN_EXPIRY_LEAD_HOURS', 'INVITE_VALID_HOURS', 'MAX_MEALS', 'CONTACT_PHONE'];
    private const MAP = ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9'];

    public function handle(Request $request, Closure $next): Response
    {
        $fix = [];
        foreach (self::KEYS as $k) {
            $v = $request->input($k);
            if (is_string($v)) {
                $fix[$k] = strtr($v, self::MAP);
            }
        }
        if ($fix) {
            $request->merge($fix);
        }

        return $next($request);
    }
}
