<?php

namespace Tests\Feature;

use App\Models\UserAccount;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use Concerns;

    private function payload(array $over = []): array
    {
        return array_merge([
            'full_name' => 'নতুন দাতা', 'email' => 'New.Donor@Example.com', 'phone' => '8801811111111', 'donor_type' => 'Individual',
            'thana_id' => $this->coveredThana()->thana_id, 'address_line' => 'রোড ১, বাড়ি ৫',
            'password' => 'Strong@123', 'password_confirmation' => 'Strong@123', 'terms' => '1',
        ], $over);
    }

    public function test_donor_registers_with_normalized_phone_lowercase_email_hashed_password_and_donor_id(): void
    {
        $this->seedDemo();
        $this->post('/register', $this->payload())->assertRedirect('/login/donor');
        $u = UserAccount::where('email', 'new.donor@example.com')->firstOrFail();
        $this->assertSame('+8801811111111', $u->phone);
        $this->assertTrue(Hash::check('Strong@123', $u->password_hash));
        $this->assertNotSame('Strong@123', $u->password_hash);
        $this->assertSame('DNR-000002', $u->donor->donor_id); // demo donor is DNR-000001
    }

    public function test_rejects_weak_password_bad_phone_duplicate_email_and_missing_terms(): void
    {
        $this->seedDemo();
        $this->post('/register', $this->payload(['password' => 'weakpass', 'password_confirmation' => 'weakpass']))->assertSessionHasErrors('password');
        $this->post('/register', $this->payload(['phone' => '01212345678']))->assertSessionHasErrors('phone');
        $this->post('/register', $this->payload(['email' => 'donor@kyaf.local']))->assertSessionHasErrors('email');
        $this->post('/register', $this->payload(['phone' => '01700000005']))->assertSessionHasErrors('phone');
        $this->post('/register', $this->payload(['terms' => null]))->assertSessionHasErrors('terms');
        $this->post('/register', $this->payload(['donor_type' => 'Restaurant']))->assertSessionHasErrors('organization_name');
        $this->post('/register', $this->payload(['password_confirmation' => 'Different@1']))->assertSessionHasErrors('password');
    }
}
