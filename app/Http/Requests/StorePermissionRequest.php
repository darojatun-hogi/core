<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Role;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('managePermissions', Role::class);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|starts_with:' . Role::appPrefix() . '|unique:supabase.permissions,name',
        ];
    }
}
