<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

if (! function_exists('bn_digits')) {
    /** Convert Western digits to Bangla digits for display. */
    function bn_digits($value): string
    {
        return strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    }
}

if (! function_exists('bst')) {
    /** Format a UTC timestamp in Bangladesh Standard Time with Bangla digits. */
    function bst($dt, string $format = 'd/m/Y h:i A'): string
    {
        if (! $dt) {
            return '—';
        }
        $out = Carbon::parse($dt)->timezone(config('app.display_timezone'))->format($format);

        return bn_digits(str_replace(['AM', 'PM'], ['পূর্বাহ্ণ', 'অপরাহ্ণ'], $out));
    }
}

if (! function_exists('setting')) {
    /** Read an administrator-configurable setting, falling back to config/kyaf.php defaults. */
    function setting(string $key, $default = null)
    {
        try {
            $all = cache()->driver('array')->rememberForever('kyaf.settings', fn () => \App\Models\SystemSetting::query()->pluck('setting_value', 'setting_key')->all());
        } catch (\Throwable $e) {
            $all = [];
        }

        return $all[$key] ?? config('kyaf.defaults.'.$key, $default);
    }
}

if (! function_exists('flush_settings_cache')) {
    function flush_settings_cache(): void
    {
        try {
            cache()->driver('array')->forget('kyaf.settings');
        } catch (\Throwable $e) {
        }
    }
}
