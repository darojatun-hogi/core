<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Permission;
use App\Models\Role;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Role::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|starts_with:' . Role::appPrefix() . '|unique:supabase.roles,name',
            'permissions' => 'array',
            'permissions.*' => [Rule::in(Permission::forApp()->pluck('name'))],
        ];
    }
}
