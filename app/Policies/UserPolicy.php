<?php

namespace App\Policies;

use App\Support\ManagedApp;
use App\Models\User;

// Akun dipakai semua app: admin app hanya boleh menyentuh user yang punya akses ke app yang sedang dikelola
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view') && ManagedApp::canManage($user);
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view') && ManagedApp::canManage($user) && $this->belongsToManagedApp($model);
    }

    public function create(User $user): bool
    {
        return $user->can('users.create') && ManagedApp::canManage($user);
    }

    public function update(User $user, User $model): bool
    {
        if ($model->hasRole(config('access.super_admin_role')) && ! $user->hasRole(config('access.super_admin_role'))) {
            return false;
        }
        return $user->can('users.edit') && ManagedApp::canManage($user) && $this->belongsToManagedApp($model);
    }

    public function delete(User $user, User $model): bool
    {
        if ($model->hasRole(config('access.super_admin_role')) && ! $user->hasRole(config('access.super_admin_role'))) {
            return false;
        }

        return $user->can('users.delete') && ManagedApp::canManage($user) && $user->id !== $model->id
            && $this->belongsToManagedApp($model);
    }

    private function belongsToManagedApp(User $model): bool
    {
        return User::withAppAccess(ManagedApp::current())->whereKey($model->id)->exists();
    }
}
