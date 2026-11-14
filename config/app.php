<?php

return [
    'name' => env('APP_NAME', 'KYAF খাদ্য দান ব্যবস্থাপনা'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => 'UTC',
    'display_timezone' => 'Asia/Dhaka',
    'locale' => env('APP_LOCALE', 'bn'),
    'fallback_locale' => 'bn',
    'faker_locale' => 'en_US',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => [],
    'maintenance' => ['driver' => 'file'],
];
