<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AppAccessSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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

    public function test_root_shows_landing_page_with_apps(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Satu akun untuk semua aplikasi Elips.')
            ->assertSee(url('/login'))
            ->assertSee('money.elips.id');
    }

    public function test_landing_page_offers_dashboard_when_logged_in(): void
    {
        $this->actingAs($this->makeUser())->get('/')
            ->assertOk()
            ->assertSee(url('/dashboard'))
            ->assertDontSee(url('/register'));
    }

    public function test_logout_signs_out_and_shows_login(): void
    {
        $this->actingAs($this->makeUser())->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // Logout hanya mengeluarkan device ini: remember_token tetap supaya "remember me" di device lain masih berlaku
    public function test_logout_keeps_remember_me_on_other_devices(): void
    {
        $user = $this->makeUser();
        $this->post('/login', ['login' => 'hogi', 'password' => 'secret-password', 'remember' => '1']);
        $token = $user->fresh()->remember_token;

        $this->post('/logout');

        $this->assertGuest();
        $this->assertNotNull($token);
        $this->assertSame($token, $user->fresh()->remember_token);
    }

    public function test_password_change_logs_out_other_devices(): void
    {
        config(['session.driver' => 'database']);
        $user = $this->makeUser();
        $other = $this->makeUser(['username' => 'other', 'email' => 'other@example.com']);
        $user->setRememberToken('old-token');
        $user->save();

        $sessions = DB::connection(config('session.connection'))->table(config('session.table'));
        foreach ([['hp', $user->id], ['laptop', $user->id], ['lain', $other->id]] as [$id, $userId]) {
            $sessions->insert(['id' => $id, 'user_id' => $userId, 'payload' => '', 'last_activity' => time()]);
        }

        $user->update(['password' => 'new-password']);

        $this->assertNotSame('old-token', $user->fresh()->remember_token);
        $this->assertSame(['lain'], $sessions->pluck('id')->all());
    }

    // Device yang dipakai untuk ganti password tetap login: cookie "remember me" diterbitkan ulang dengan token baru
    public function test_own_password_change_renews_remember_me_cookie(): void
    {
        $user = $this->makeUser();
        $recaller = auth()->guard()->getRecallerName();

        $this->actingAs($user)
            ->withCookie($recaller, 'old')
            ->put('/profile/password', [
                'current_password' => 'secret-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasNoErrors()
            ->assertCookie($recaller);

        $this->assertAuthenticatedAs($user);
    }
}
