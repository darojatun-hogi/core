<?php

namespace App\Http\Requests;

use App\Support\ManagedApp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Permission;
use App\Models\Role;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('role'));
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|starts_with:' . Role::appPrefix(ManagedApp::current()) . '|unique:supabase.roles,name,' . $this->route('role')->id,
            'permissions' => 'array',
            'permissions.*' => [Rule::in(Permission::forApp(ManagedApp::current())->pluck('name'))],
        ];
    }
}
