<?php

namespace Tests\Unit;

use App\Support\Phone;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    public function test_accepts_all_three_formats_and_normalizes(): void
    {
        $this->assertSame('+8801712345678', Phone::normalize('01712345678'));
        $this->assertSame('+8801712345678', Phone::normalize('8801712345678'));
        $this->assertSame('+8801712345678', Phone::normalize('+8801712345678'));
        $this->assertSame('+8801712345678', Phone::normalize('017-1234 5678'));
    }

    public function test_accepts_bangla_digits(): void
    {
        $this->assertSame('+8801712345678', Phone::normalize('০১৭১২৩৪৫৬৭৮'));
    }

    public function test_rejects_invalid_numbers(): void
    {
        $this->assertNull(Phone::normalize('01212345678')); // 012 is not a mobile prefix
        $this->assertNull(Phone::normalize('0171234567'));  // too short
        $this->assertNull(Phone::normalize('abc'));
        $this->assertNull(Phone::normalize('+919876543210'));
    }
}
