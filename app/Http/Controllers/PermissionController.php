<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        
        if ($permission->roles()->count() > 0) {
            return back()->with('error', 'Permission used by one or more roles.');
        }

        $permission->delete();
        return redirect()->route('roles.index')->with('success', 'Permission successfully deleted.');
    }
}
