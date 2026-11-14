<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $v = (string) $value;
        $len = mb_strlen($v);
        if ($len < 8 || $len > 64) {
            $fail('পাসওয়ার্ড ৮ থেকে ৬৪ অক্ষরের মধ্যে হতে হবে।');

            return;
        }
        if (! preg_match('/[A-Z]/', $v) || ! preg_match('/[a-z]/', $v) || ! preg_match('/\d/', $v) || ! preg_match('/[^A-Za-z0-9]/', $v)) {
            $fail('পাসওয়ার্ডে অবশ্যই একটি বড় হাতের অক্ষর, একটি ছোট হাতের অক্ষর, একটি সংখ্যা ও একটি বিশেষ চিহ্ন থাকতে হবে।');
        }
    }
}
