<?php

namespace Database\Seeders;

use App\Console\Commands\LocalSetup;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Pengguna demo untuk mode lokal tanpa SSO (docs/lokal-tanpa-sso.md). File ini sama di semua app Elips; daftar pengguna
 * dan paketnya ada di config/local.php, kata sandinya LOCAL_DEMO_PASSWORD (bawaan "password"). Hanya berjalan di
 * APP_ENV=local dengan database lokal (LocalSetup::problem), dan dipanggil oleh app:local-setup setelah seeder role.
 */
class LocalDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Sama dengan app:local-setup: tolak bila bukan APP_ENV=local atau database pengguna bukan database lokal
        if ($problem = LocalSetup::problem()) {
            $this->command?->error('LocalDemoSeeder dibatalkan. '.$problem);

            return;
        }

        $password = Hash::make((string) config('local.demo_password', 'password'));

        foreach (config('local.demo_users', []) as [$name, $username, $plan]) {
            $user = User::where('username', $username)->first() ?? new User;
            $user->forceFill([
                'name' => $name,
                'username' => $username,
                'email' => $user->email ?: $username.'@lokal.test',
                'password' => $password,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            $this->applyPlan($user, $plan);
            $this->command?->line("  {$user->username} ({$user->email}): paket $plan");
        }
    }

    /** Role dari paket, dan langganan aktif untuk paket berbayar (hanya app yang punya model Subscription). */
    private function applyPlan(User $user, string $plan): void
    {
        $config = config("local.plans.$plan", []);
        $user->syncRoles($config['roles'] ?? []);

        $days = $config['subscription_days'] ?? null;
        $subscription = 'App\\Models\\Subscription';
        if ($days && class_exists($subscription)) {
            $subscription::updateOrCreate(['user_id' => $user->id], ['ends_at' => now()->addDays((int) $days)]);
        }
    }
}
