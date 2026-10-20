<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\Role;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.edit');
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name'      => ['required', 'string', 'max:255'],
            'username'  => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('supabase.users', 'username')->ignore($userId)],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('supabase.users', 'email')->ignore($userId)],
            'password'  => ['nullable', 'string', Password::defaults(), 'confirmed'],
            'roles'     => ['required', 'array', 'min:1'],
            // super-admin tidak bisa diberikan dari app; hanya role milik app ini
            'roles.*'   => [Rule::in(Role::forApp()->pluck('name'))],
        ];
    }
}
