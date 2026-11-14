<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use Tests\Concerns;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use Concerns;

    private function login(string $slug, string $id, string $pw)
    {
        return $this->post("/login/{$slug}", ['identifier' => $id, 'password' => $pw]);
    }

    public function test_each_role_logs_in_on_its_own_page_and_lands_on_its_dashboard(): void
    {
        $this->seedDemo();
        $cases = [['admin', 'admin@kyaf.local', 'Admin@12345', '/admin/dashboard'], ['manager', 'manager@kyaf.local', 'Manager@12345', '/manager/dashboard'],
            ['staff', 'staff1@kyaf.local', 'Staff@12345', '/staff/dashboard'], ['donor', 'donor@kyaf.local', 'Donor@12345', '/donor/dashboard']];
        foreach ($cases as [$slug, $email, $pw, $dash]) {
            $this->login($slug, $email, $pw)->assertRedirect('/dashboard');
            $this->get('/dashboard')->assertRedirect($dash);
            $this->get($dash)->assertOk();
            $this->post('/logout')->assertRedirect('/');
        }
    }

    public function test_login_by_mobile_number_works(): void
    {
        $this->seedDemo();
        $this->login('donor', '01700000005', 'Donor@12345')->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_a_role_cannot_log_in_through_another_roles_page(): void
    {
        $this->seedDemo();
        $this->login('admin', 'donor@kyaf.local', 'Donor@12345')->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_wrong_password_gives_generic_message(): void
    {
        $this->seedDemo();
        $r = $this->login('donor', 'donor@kyaf.local', 'Wrong@12345');
        $r->assertSessionHas('error');
        $this->assertGuest();
        $this->login('donor', 'nobody@kyaf.local', 'Wrong@12345')->assertSessionHas('error');
    }

    public function test_account_locks_after_five_failures(): void
    {
        $this->seedDemo();
        for ($i = 0; $i < 5; $i++) {
            $this->login('donor', 'donor@kyaf.local', 'Wrong@12345');
        }
        $this->assertSame('Locked', $this->user('donor@kyaf.local')->fresh()->status);
        $this->login('donor', 'donor@kyaf.local', 'Donor@12345')->assertSessionHas('error');
        $this->assertGuest();
        $this->travel(16)->minutes();
        $this->login('donor', 'donor@kyaf.local', 'Donor@12345')->assertRedirect('/dashboard');
    }

    public function test_dashboards_are_protected_by_role_and_denials_are_logged(): void
    {
        $this->seedDemo();
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->login('donor', 'donor@kyaf.local', 'Donor@12345');
        foreach (['/admin/dashboard', '/manager/dashboard', '/staff/dashboard', '/admin/users', '/admin/audit-logs'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->assertTrue(AuditLog::where('action_type', 'ACCESS_DENIED')->where('result', 'Failure')->exists());
    }

    public function test_idle_session_expires_after_30_minutes(): void
    {
        $this->seedDemo();
        $this->login('donor', 'donor@kyaf.local', 'Donor@12345');
        $this->get('/donor/dashboard')->assertOk();
        $this->travel(31)->minutes();
        $this->get('/donor/dashboard')->assertRedirect('/login');
    }

    public function test_session_id_is_regenerated_on_login(): void
    {
        $this->seedDemo();
        $this->get('/login/donor');
        $before = session()->getId();
        $this->login('donor', 'donor@kyaf.local', 'Donor@12345');
        $this->assertNotSame($before, session()->getId());
    }
}
