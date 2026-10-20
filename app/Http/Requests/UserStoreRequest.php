<?php

namespace App\Http\Requests;

use App\Support\ManagedApp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Models\Role;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('users.create') && ManagedApp::canManage($this->user());
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:supabase.users,username', 'alpha_dash'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:supabase.users,email'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'roles' => ['required', 'array', 'min:1'],
            // super-admin tidak bisa diberikan dari sini; hanya role milik app yang dikelola
            'roles.*' => [Rule::in(Role::forApp(ManagedApp::current())->pluck('name'))],
        ];
    }
}
