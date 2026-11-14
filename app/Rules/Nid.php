<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Nid implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^(\d{10}|\d{13}|\d{17})$/', (string) $value)) {
            $fail('জাতীয় পরিচয়পত্র নম্বর ১০, ১৩ বা ১৭ অঙ্কের হতে হবে।');
        }
    }
}
