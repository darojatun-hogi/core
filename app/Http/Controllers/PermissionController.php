<?php

namespace App\Http\Controllers;

use App\Support\ManagedApp;
use App\Http\Requests\StorePermissionRequest;
use App\Models\Permission;
use App\Models\Role;

class PermissionController extends Controller
{
    public function store(StorePermissionRequest $request)
    {
        Permission::create(['name' => $request->name]);

        return redirect()->route('roles.index')->with('success', 'Successfully added permission.');
    }

    public function destroy(Permission $permission)
    {
        $this->authorize('managePermissions', Role::class);
        abort_unless(str_starts_with($permission->name, Role::appPrefix(ManagedApp::current())), 404);

        if ($permission->roles()->count() > 0) {
            return back()->with('error', 'Permission used by one or more roles.');
        }

        $permission->delete();

        return redirect()->route('roles.index')->with('success', 'Permission successfully deleted.');
    }
}
