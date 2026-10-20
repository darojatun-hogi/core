<?php

namespace App\Http\Controllers;

use App\Support\Sso;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Hanya di hub SSO. Status verifikasi (users.email_verified_at) berlaku untuk semua app.
class EmailVerificationController extends Controller
{
    public function notice(Request $request)
    {
        if ($redirect = Sso::safeRedirect($request->query('redirect'))) {
            $request->session()->put('sso.redirect', $redirect);
        }

        if ($request->user()->hasVerifiedEmail()) {
            return $this->continue($request);
        }

        return view('auth.verify-email');
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();

        return $this->continue($request)->with('success', 'Email berhasil diverifikasi!');
    }

    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->continue($request);
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Link verifikasi telah dikirim ulang ke email kamu.');
    }

    private function continue(Request $request)
    {
        if ($redirect = Sso::safeRedirect($request->session()->pull('sso.redirect'))) {
            return redirect()->away($redirect);
        }

        return redirect()->route('dashboard');
    }
}
