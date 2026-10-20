<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

// Daftarkan app ini ke skema role/permission bersama di Supabase. Aman dijalankan berulang dan tidak
// menyentuh role/permission app lain. Jalankan sekali saat membuat project baru:
//   php artisan db:seed --class=AppAccessSeeder
class AppAccessSeeder extends Seeder
{
    // Permission fitur milik app ini, tanpa awalan (otomatis jadi "<app>.<nama>").
    // Contoh untuk app laporan: 'reports.view', 'reports.create', 'reports.edit', 'reports.delete'
    private array $appPermissions = [];

    // Bagian dari $appPermissions yang juga diberikan ke role <app>.user
    private array $userPermissions = [];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $prefix = Role::appPrefix();
        $access = $prefix . 'access';
        $appPermissions = array_map(fn (string $name) => $prefix . $name, $this->appPermissions);

        foreach ([$access, ...$appPermissions, ...config('access.shared_permissions')] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // super-admin: semua akses di semua app lewat Gate::before, tanpa daftar permission
        Role::firstOrCreate(['name' => config('access.super_admin_role'), 'guard_name' => 'web']);

        $admin = Role::firstOrCreate(['name' => $prefix . 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([$access, ...$appPermissions, ...config('access.shared_permissions')]);

        $user = Role::firstOrCreate(['name' => $prefix . 'user', 'guard_name' => 'web']);
        $user->syncPermissions([$access, ...array_map(fn (string $name) => $prefix . $name, $this->userPermissions)]);

        // Opsional: beri <app>.admin ke akun yang sudah ada (APP_ADMIN_EMAIL di .env)
        if ($email = config('access.admin_email')) {
            User::where('email', $email)->first()?->assignRole($admin);
        }

        $this->command?->info("Role {$prefix}admin, {$prefix}user dan permission {$access} siap.");
    }
}
