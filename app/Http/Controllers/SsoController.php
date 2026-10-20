<?php

namespace App\Http\Controllers;

use App\Support\Sso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Dipakai app client (SSO_HUB=false): login, register, reset password, dan verifikasi email ditangani hub.
// Nama route tetap sama (login, register, ...) supaya middleware auth/verified bawaan Laravel tetap bekerja.
class SsoController extends Controller
{
    public function login(Request $request)
    {
        if (Auth::check()) {
            return redirect()->intended(url(config('sso.home')));
        }

        return redirect()->away(Sso::hubUrl('login', ['redirect' => $this->returnUrl($request)]));
    }

    public function register(Request $request)
    {
        return redirect()->away(Sso::hubUrl('register', ['redirect' => $this->returnUrl($request)]));
    }

    public function forgotPassword()
    {
        return redirect()->away(Sso::hubUrl('forgot-password'));
    }

    public function verifyEmail(Request $request)
    {
        return redirect()->away(Sso::hubUrl('email/verify', ['redirect' => $this->returnUrl($request)]));
    }

    // Session dipakai bersama semua app, jadi logout di sini juga keluar dari semua app
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away(Sso::hubUrl('login', ['redirect' => url(config('sso.home'))]));
    }

    // Halaman yang tadi ingin dibuka (disimpan middleware auth), atau halaman awal app ini
    private function returnUrl(Request $request): string
    {
        return $request->session()->pull('url.intended', url(config('sso.home')));
    }
}
