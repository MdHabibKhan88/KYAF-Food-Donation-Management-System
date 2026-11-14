<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use Tests\Concerns;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use Concerns;

    public function test_public_pages_load(): void
    {
        $this->seedDemo();
        foreach (['/', '/about', '/find-food', '/contact', '/become-staff', '/register', '/login', '/forgot-password'] as $url) {
            $this->get($url)->assertOk();
        }
        foreach (['admin', 'manager', 'staff', 'donor'] as $role) {
            $this->get("/login/{$role}")->assertOk();
        }
        $this->get('/does-not-exist')->assertNotFound();
    }

    public function test_location_endpoints_return_json(): void
    {
        $this->seedDemo();
        $this->getJson('/locations/divisions')->assertOk()->assertJsonFragment(['ডেমো বিভাগ']);
    }

    public function test_contact_form_saves_message(): void
    {
        $this->seedDemo();
        $this->withSession(['captcha_answer' => 7])->post('/contact', [
            'sender_name' => 'পরীক্ষক', 'email' => 'visitor@example.com', 'phone' => '01712345678',
            'subject' => 'পরীক্ষা বার্তা', 'message_body' => 'এটি একটি পরীক্ষামূলক বার্তা।', 'captcha' => '7',
        ])->assertRedirect('/contact');
        $this->assertDatabaseHas('contact_messages', ['email' => 'visitor@example.com', 'phone' => '+8801712345678']);
        $this->assertSame(1, ContactMessage::count());
    }

    public function test_contact_form_rejects_wrong_captcha_and_bad_phone(): void
    {
        $this->seedDemo();
        $this->withSession(['captcha_answer' => 7])->post('/contact', [
            'sender_name' => 'X Y', 'email' => 'v@example.com', 'phone' => '12345', 'subject' => 'abc', 'message_body' => 'পর্যাপ্ত দৈর্ঘ্যের বার্তা', 'captcha' => '9',
        ])->assertSessionHasErrors(['phone']);
        $this->assertSame(0, ContactMessage::count());
    }
}
