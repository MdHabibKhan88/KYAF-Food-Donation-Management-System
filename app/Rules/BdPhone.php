<?php

namespace App\Rules;

use App\Support\Phone;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BdPhone implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Phone::normalize((string) $value) === null) {
            $fail('সঠিক বাংলাদেশি মোবাইল নম্বর দিন (যেমন: 01712345678 বা +8801712345678)।');
        }
    }
}
