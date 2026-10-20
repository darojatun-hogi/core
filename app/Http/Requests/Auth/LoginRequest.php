<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\Turnstile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'cf-turnstile-response' => Turnstile::enabled() ? ['required', new Turnstile] : ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'cf-turnstile-response.required' => 'Selesaikan verifikasi keamanan terlebih dahulu.',
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $field = filter_var($this->input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Postgres membedakan huruf besar/kecil, jadi email/username dibandingkan dalam huruf kecil
        $user = User::whereRaw("lower({$field}) = ?", [Str::lower(trim($this->input('login')))])->first();

        if (! $user || ! Auth::attempt(
            ['id' => $user->id, 'password' => $this->input('password')],
            $this->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login' => 'Wrong credentials.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => "Too many login attempts. Please try again in {$seconds} seconds.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login')).'|'.$this->ip());
    }
}