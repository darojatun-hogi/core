<?php

namespace App\Http\Requests\Auth;

use App\Rules\Turnstile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:255', 'unique:supabase.users,username', 'alpha_dash'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:supabase.users,email'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'cf-turnstile-response' => Turnstile::enabled() ? ['required', new Turnstile] : ['nullable'],
        ];
    }

    public function messages(): array
    {
        return [
            'cf-turnstile-response.required' => 'Selesaikan verifikasi keamanan terlebih dahulu.',
        ];
    }
}