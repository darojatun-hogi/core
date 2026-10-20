<?php

namespace App\Support;

use App\Models\User;

// App yang role/permission/user-nya dikelola dari halaman Users & Roles.
// Hub SSO mengelola dirinya sendiri + semua app client (config sso.apps); app standalone hanya dirinya sendiri.
// Delegasi: super-admin mengelola semua app, "<app>.admin" hanya app miliknya.
class ManagedApp
{
    public static function all(): array
    {
        $clients = Sso::isHub() ? array_column(config('sso.apps'), 'key') : [];

        return array_values(array_unique([config('access.app'), ...$clients]));
    }

    public static function for(User $user): array
    {
        if ($user->hasRole(config('access.super_admin_role'))) {
            return static::all();
        }

        return array_values(array_filter(static::all(), fn (string $app) => $user->hasRole($app.'.admin')));
    }

    public static function canManage(User $user, ?string $app = null): bool
    {
        return in_array($app ?? static::current(), static::for($user), true);
    }

    // App yang sedang dipilih (session "managed_app", diisi lewat ?app=); default app pertama yang boleh dikelola
    public static function current(): string
    {
        $apps = auth()->user() ? static::for(auth()->user()) : [];
        $selected = session('managed_app');

        return in_array($selected, $apps, true) ? $selected : ($apps[0] ?? config('access.app'));
    }

    public static function label(string $app): string
    {
        $client = collect(config('sso.apps'))->firstWhere('key', $app);

        return $client['name'] ?? ucfirst($app);
    }
}
