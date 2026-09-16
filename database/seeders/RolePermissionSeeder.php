<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.manage',
            'activity-log.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo(['users.view', 'users.create', 'users.edit', 'roles.manage', 'activity-log.view']);

        Role::firstOrCreate(['name' => 'User']);

        $userAdmin = User::where('email', 'admin@example.com')->first();
        if ($userAdmin) {
            $userAdmin->assignRole('Super Admin');
        }
    }
}
