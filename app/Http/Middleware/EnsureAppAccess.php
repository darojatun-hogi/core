<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Tabel users dipakai bersama banyak app: hanya user dengan permission "<app>.access" yang boleh masuk
class EnsureAppAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->can(config('access.app').'.access')) {
            if ($request->expectsJson()) {
                abort(403, 'Akun ini tidak punya akses ke aplikasi ini.');
            }

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun ini tidak punya akses ke aplikasi ini.');
        }

        return $next($request);
    }
}
