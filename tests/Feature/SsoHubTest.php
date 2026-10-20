<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AppAccessSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

// Mode hub (SSO_HUB=true): route dipilih saat boot, jadi env diset sebelum app dibuat
class SsoHubTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        $_ENV['SSO_HUB'] = $_SERVER['SSO_HUB'] = 'true';

        parent::setUp();

        config(['services.turnstile.secret_key' => null, 'sso.allowed_domain' => 'elips.id']);
        $this->seed(AppAccessSeeder::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_ENV['SSO_HUB'], $_SERVER['SSO_HUB']);
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::create([
            'name' => 'Hogi',
            'username' => 'hogi',
            'email' => 'hogi@example.com',
            'password' => 'secret-password',
            ...$attributes,
        ]);
        $user->markEmailAsVerified();

        return $user;
    }

    public function test_login_returns_to_the_app_that_sent_the_user(): void
    {
        $this->makeUser();

        $this->get('/login?redirect='.urlencode('https://money.elips.id/wallets'))->assertOk();
        $this->post('/login', ['login' => 'HOGI@example.com', 'password' => 'secret-password'])
            ->assertRedirect('https://money.elips.id/wallets');

        $this->assertAuthenticated();
    }

    public function test_redirect_to_foreign_domain_is_ignored(): void
    {
        $this->makeUser();

        $this->get('/login?redirect='.urlencode('https://evil.com/steal'));
        $this->post('/login', ['login' => 'hogi', 'password' => 'secret-password'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_logged_in_user_opening_login_goes_straight_back(): void
    {
        $this->actingAs($this->makeUser())
            ->get('/login?redirect='.urlencode('https://clip.elips.id/dashboard'))
            ->assertRedirect('https://clip.elips.id/dashboard');
    }

    public function test_register_sends_verification_email_and_returns_after_verifying(): void
    {
        Notification::fake();

        $this->get('/register?redirect='.urlencode('https://money.elips.id/dashboard'));
        $this->post('/register', [
            'name' => 'Budi',
            'username' => 'budi',
            'email' => 'budi@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('username', 'budi')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);

        $url = URL::temporarySignedRoute('verification.verify', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->get($url)->assertRedirect('https://money.elips.id/dashboard');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_turnstile_failure_blocks_register(): void
    {
        config(['services.turnstile.secret_key' => 'secret']);
        Http::fake(['challenges.cloudflare.com/*' => Http::response(['success' => false])]);

        $this->post('/register', [
            'name' => 'Budi',
            'username' => 'budi',
            'email' => 'budi@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'cf-turnstile-response' => 'token',
        ])->assertSessionHasErrors('cf-turnstile-response');

        $this->assertDatabaseMissing('users', ['username' => 'budi'], 'supabase');
    }

    public function test_logout_signs_out_and_shows_login(): void
    {
        $this->actingAs($this->makeUser())->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
