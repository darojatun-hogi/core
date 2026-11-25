<?php

namespace Tests\Feature;

use App\Console\Commands\LocalSetup;
use App\Http\Controllers\LocalLoginController;
use App\Models\User;
use Database\Seeders\LocalDemoSeeder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Masuk lokal tanpa SSO (docs/lokal-tanpa-sso.md). File ini sama di semua app Elips. Tes yang menulis database hanya
 * berjalan bila koneksi "supabase" sudah SQLite di tes, supaya tidak pernah mengenai database pengguna sungguhan.
 */
class LocalLoginTest extends TestCase
{
    public function test_local_login_is_off_outside_the_local_environment(): void
    {
        config(['sso.local_login' => true]);

        $this->assertFalse(LocalLoginController::enabled());
    }

    public function test_local_login_needs_the_flag_even_in_local(): void
    {
        $this->app['env'] = 'local';
        config(['sso.local_login' => false]);
        $this->assertFalse(LocalLoginController::enabled());

        config(['sso.local_login' => true]);
        $this->assertTrue(LocalLoginController::enabled());
    }

    public function test_setup_command_refuses_outside_local(): void
    {
        $this->artisan('app:local-setup')->expectsOutputToContain('Hanya untuk APP_ENV=local')->assertFailed();
    }

    // Pengaman yang sama dipakai LocalDemoSeeder: database pengguna di server lain tidak pernah ditulis
    public function test_setup_and_demo_seeder_refuse_a_remote_user_database(): void
    {
        $this->app['env'] = 'local';
        config(['database.connections.supabase' => ['driver' => 'pgsql', 'host' => 'db.example.supabase.co', 'database' => 'postgres']]);

        $this->assertStringContainsString('bukan database lokal', (string) LocalSetup::problem());
    }

    public function test_demo_users_log_in_with_the_demo_password_and_get_their_roles(): void
    {
        $plans = config('local.plans');
        $this->assertNotEmpty($plans);
        foreach (config('local.demo_users') as [, , $plan]) {
            $this->assertArrayHasKey($plan, $plans);
        }

        if (config('database.connections.supabase.driver') !== 'sqlite') {
            $this->markTestSkipped('Koneksi "supabase" di tes bukan SQLite; tes yang menulis pengguna dilewati.');
        }
        if (! Schema::connection('supabase')->hasTable('users')) {
            $this->artisan('migrate');
        }
        $this->seed(config('local.seeders')[0]);
        $this->app['env'] = 'local';
        config(['local.demo_password' => 'rahasia-uji']);
        $this->seed(LocalDemoSeeder::class);
        $this->app['env'] = 'testing'; // kembali ke testing supaya pemeriksaan CSRF tetap dilewati seperti tes lain
        Route::middleware('web')->post('/_uji/login-lokal', [LocalLoginController::class, 'login']);

        [, $username, $plan] = config('local.demo_users')[0];
        $this->post('/_uji/login-lokal', ['login' => $username, 'password' => 'salah'])->assertSessionHasErrors('login');
        $this->assertGuest();

        $this->post('/_uji/login-lokal', ['login' => $username, 'password' => 'rahasia-uji'])->assertRedirect();
        $user = User::where('username', $username)->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->hasAllRoles($plans[$plan]['roles']));
    }
}
