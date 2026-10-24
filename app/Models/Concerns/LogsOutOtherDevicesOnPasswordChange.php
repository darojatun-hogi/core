<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Ganti password = keluar dari semua device lain (profil, reset password, admin edit user).
// Logout biasa tidak mengganti remember_token (logoutCurrentDevice), jadi di sinilah device lain dikeluarkan:
// remember_token baru membatalkan cookie "remember me", dan session lain milik user dihapus.
trait LogsOutOtherDevicesOnPasswordChange
{
    protected static function bootLogsOutOtherDevicesOnPasswordChange(): void
    {
        static::updating(function ($user) {
            if ($user->isDirty('password')) {
                $user->setRememberToken(Str::random(60));
            }
        });

        static::updated(function ($user) {
            if (! $user->wasChanged('password')) {
                return;
            }

            $request = request();
            $currentSession = $request->hasSession() ? $request->session()->getId() : null;

            if (config('session.driver') === 'database') {
                DB::connection(config('session.connection'))->table(config('session.table'))
                    ->where('user_id', $user->getAuthIdentifier())
                    ->when($currentSession, fn ($q) => $q->where('id', '!=', $currentSession))
                    ->delete();
            }

            // User mengganti password-nya sendiri: terbitkan ulang cookie "remember me" supaya device ini tetap login
            $guard = Auth::guard();
            if ($guard->id() === $user->getAuthIdentifier() && $request->hasCookie($guard->getRecallerName())) {
                $guard->login($user, true);
            }
        });
    }
}
