<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Support\Sso;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserRegisteredNotification;

// Hanya dipakai di hub SSO (SSO_HUB=true). App client mengarahkan halaman ini ke hub lewat SsoController.
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $this->rememberRedirect($request);

        if (Auth::check()) {
            return $this->redirectAfterLogin($request);
        }
        return view('auth.login');
    }

    public function authenticate(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();
        $request->session()->put('sso.login_at', now()->timestamp);

        return $this->redirectAfterLogin($request);
    }

    public function register(Request $request)
    {
        $this->rememberRedirect($request);

        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        Notification::send(
            User::notifiableAdmins(except: Auth::user()),
            new UserRegisteredNotification($user)
        );

        $user->sendEmailVerificationNotification();

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('sso.login_at', now()->timestamp);

        return redirect()->route('verification.notice')
            ->with('success', 'Registrasi berhasil! Cek email kamu untuk verifikasi akun.');
    }

    // Session dipakai bersama semua app, jadi logout di sini juga keluar dari semua app
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirect = Sso::safeRedirect($request->input('redirect'));

        return redirect()->route('login', $redirect ? ['redirect' => $redirect] : []);
    }

    private function rememberRedirect(Request $request): void
    {
        if ($redirect = Sso::safeRedirect($request->query('redirect'))) {
            $request->session()->put('sso.redirect', $redirect);
        }
    }

    // Kembali ke app asal (?redirect=) bila ada, selain itu ke dashboard hub
    private function redirectAfterLogin(Request $request)
    {
        if ($redirect = Sso::safeRedirect($request->session()->pull('sso.redirect'))) {
            return redirect()->away($redirect);
        }

        return redirect()->intended(route('dashboard'));
    }
}
