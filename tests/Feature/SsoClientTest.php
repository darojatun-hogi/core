<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AppAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Mode client (default): login/register/verifikasi diarahkan ke hub
class SsoClientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['sso.hub_url' => 'https://auth.elips.id']);
        $this->seed(AppAccessSeeder::class);
    }

    // User dari app lain: belum punya role app ini
    private function makeOutsider(bool $verified = true): User
    {
        $user = User::create(['name' => 'Hogi', 'username' => 'hogi', 'email' => 'hogi@example.com', 'password' => 'secret-password']);
        $user->syncRoles([]);

        if ($verified) {
            $user->markEmailAsVerified();
        }

        return $user;
    }

    public function test_guest_is_sent_to_hub_with_return_url(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/login')->assertRedirect('https://auth.elips.id/login?redirect='.urlencode(url('/dashboard')));
    }

    public function test_register_and_forgot_password_go_to_hub(): void
    {
        $this->get('/register')->assertRedirect('https://auth.elips.id/register?redirect='.urlencode(url('/dashboard')));
        $this->get('/forgot-password')->assertRedirect('https://auth.elips.id/forgot-password');
    }

    public function test_user_from_another_app_is_granted_app_user_role(): void
    {
        $user = $this->makeOutsider();

        $this->actingAs($user)->get('/dashboard')->assertOk();
        $this->assertTrue($user->fresh()->hasRole('core.user'));
    }

    public function test_without_auto_grant_user_sees_403_but_stays_signed_in(): void
    {
        config(['access.auto_grant' => false]);
        $user = $this->makeOutsider();

        $this->actingAs($user)->get('/dashboard')->assertForbidden();
        $this->assertAuthenticatedAs($user);
    }

    public function test_unverified_user_is_sent_to_hub_verification(): void
    {
        $user = $this->makeOutsider(verified: false);

        $this->actingAs($user)->get('/dashboard')->assertRedirect('/email/verify');
        $this->get('/email/verify')->assertRedirect('https://auth.elips.id/email/verify?redirect='.urlencode(url('/dashboard')));
    }

    public function test_user_management_lives_in_the_hub(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('users.index'));

        $admin = $this->makeOutsider();
        $admin->assignRole('core.admin');

        $this->actingAs($admin)->get('/access')->assertRedirect('https://auth.elips.id/users?app=core');
    }

    public function test_plain_user_cannot_open_access_link(): void
    {
        $user = $this->makeOutsider();
        $user->assignRole('core.user');

        $this->actingAs($user)->get('/access')->assertForbidden();
    }

    public function test_logout_returns_to_hub_login(): void
    {
        $this->actingAs($this->makeOutsider())
            ->post('/logout')
            ->assertRedirect('https://auth.elips.id/login?redirect='.urlencode(url('/dashboard')));
        $this->assertGuest();
    }
}
