<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Tabel users dipakai bersama banyak app: hanya user dengan permission "<app>.access" yang boleh masuk.
// Dengan SSO, user yang login dari app lain otomatis diberi role "<app>.user" (ACCESS_AUTO_GRANT).
// User tidak di-logout di sini karena session dipakai bersama: logout akan mengeluarkannya dari semua app.
class EnsureAppAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $permission = config('access.app').'.access';

        if ($user && ! $user->can($permission) && config('access.auto_grant')) {
            $role = Role::where('name', Role::appPrefix().'user')->where('guard_name', 'web')->first();

            if ($role) {
                $user->assignRole($role);
                $user->unsetRelation('permissions');
            }
        }

        if ($user && ! $user->can($permission)) {
            abort(403, 'Akun ini tidak punya akses ke aplikasi ini.');
        }

        return $next($request);
    }
}
