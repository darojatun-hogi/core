<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;

// Verifikasi token Cloudflare Turnstile. Dilewati bila TURNSTILE_SECRET_KEY kosong (dev lokal).
class Turnstile implements ValidationRule
{
    public static function enabled(): bool
    {
        return filled(config('services.turnstile.secret_key'));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! static::enabled()) {
            return;
        }

        $response = Http::asForm()->timeout(10)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => config('services.turnstile.secret_key'),
            'response' => (string) $value,
            'remoteip' => request()->ip(),
        ]);

        if (! $response->json('success')) {
            $fail('Verifikasi keamanan gagal. Silakan coba lagi.');
        }
    }
}
