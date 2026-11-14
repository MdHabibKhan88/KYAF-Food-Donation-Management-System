<?php

namespace Tests\Unit;

use App\Rules\BdPhone;
use App\Rules\Nid;
use App\Rules\StrongPassword;
use PHPUnit\Framework\TestCase;

class RulesTest extends TestCase
{
    private function passes($rule, $value): bool
    {
        $failed = false;
        $rule->validate('f', $value, function () use (&$failed) { $failed = true; });

        return ! $failed;
    }

    public function test_strong_password(): void
    {
        $r = new StrongPassword;
        $this->assertTrue($this->passes($r, 'Abcdef1!'));
        $this->assertFalse($this->passes($r, 'abcdef1!'));   // no upper
        $this->assertFalse($this->passes($r, 'ABCDEF1!'));   // no lower
        $this->assertFalse($this->passes($r, 'Abcdefg!'));   // no digit
        $this->assertFalse($this->passes($r, 'Abcdefg1'));   // no special
        $this->assertFalse($this->passes($r, 'Ab1!'));       // too short
        $this->assertFalse($this->passes($r, 'Aa1!'.str_repeat('x', 61))); // 65 chars
    }

    public function test_nid_lengths(): void
    {
        $r = new Nid;
        foreach (['1234567890', '1234567890123', '12345678901234567'] as $ok) {
            $this->assertTrue($this->passes($r, $ok));
        }
        foreach (['123456789', '12345678901', '1234567890a', ''] as $bad) {
            $this->assertFalse($this->passes($r, $bad));
        }
    }

    public function test_bd_phone_rule(): void
    {
        $r = new BdPhone;
        $this->assertTrue($this->passes($r, '+8801911111111'));
        $this->assertFalse($this->passes($r, '0111111111'));
    }
}
