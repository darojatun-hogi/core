<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Masuk tanpa hub SSO, khusus pengembangan lokal (docs/lokal-tanpa-sso.md). File ini sama di semua app Elips. Tampilan
 * dan alurnya meniru halaman masuk auth.elips.id (email atau username + kata sandi), tapi memeriksa database lokal.
 * Pengguna demo dibuat oleh Database\Seeders\LocalDemoSeeder. Rutenya hanya didaftarkan bila self::enabled():
 * APP_ENV=local dan SSO_LOCAL_LOGIN=true.
 */
class LocalLoginController extends Controller
{
    public static function enabled(): bool
    {
        return (bool) config('sso.local_login') && app()->environment('local');
    }

    public function show()
    {
        if (Auth::check()) {
            return redirect()->intended(url(config('sso.home', '/')));
        }

        $usernames = collect(config('local.demo_users', []))->pluck(1);

        return view('auth.local-login', [
            'demoUsers' => User::whereIn('username', $usernames)->with('roles')->get()->sortBy(fn ($u) => $usernames->search($u->username)),
            'defaultPassword' => (string) config('local.demo_password', 'password') === 'password',
        ]);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [], ['login' => 'email atau username', 'password' => 'password']);

        $user = User::where(str_contains($data['login'], '@') ? 'email' : 'username', $data['login'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['login' => 'Email/username atau password salah.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(url(config('sso.home', '/')));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
