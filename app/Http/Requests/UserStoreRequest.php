<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\Role;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:supabase.users,username', 'alpha_dash'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:supabase.users,email'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            // super-admin tidak bisa diberikan dari app; hanya role milik app ini
            'roles.*' => [Rule::in(Role::forApp()->pluck('name'))],
        ];
    }
}
