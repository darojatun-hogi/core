<?php

namespace App\Policies;

use App\Support\ManagedApp;
use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view') && ManagedApp::canManage($user);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('roles.view') && ManagedApp::canManage($user);
    }

    public function create(User $user): bool
    {
        return $user->can('roles.create') && ManagedApp::canManage($user);
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('roles.edit') && ManagedApp::canManage($user);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('roles.delete') && ManagedApp::canManage($user);
    }

    // Membuat/menghapus permission mengubah daftar bersama semua app: khusus super-admin
    public function managePermissions(User $user): bool
    {
        return $user->hasRole(config('access.super_admin_role'));
    }
}
