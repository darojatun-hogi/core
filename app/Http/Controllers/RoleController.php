<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;

// Role terpusat di Supabase: halaman ini hanya mengelola role berawalan "<app>." (config access.app)
class RoleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Role::class);

        $search = $request->query('search');

        $roles = Role::forApp()->with('permissions')
            ->when($search, fn($q) => $q->where('name', 'ilike', "%{$search}%"))
            ->paginate(10)
            ->withQueryString();

        $permissions = Permission::forApp()->orderBy('name')->get();

        return view('roles.index', compact('roles', 'search', 'permissions'));
    }

    public function show(Role $role)
    {
        $this->ensureOwned($role);
        $this->authorize('view', $role);
        $role->load('permissions');

        return view('roles.show', compact('role'));
    }

    public function create()
    {
        $this->authorize('create', Role::class);
        $permissions = Permission::forApp()->orderBy('name')->get();

        return view('roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request)
    {
        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Successfully added role.');
    }

    public function edit(Role $role)
    {
        $this->ensureOwned($role);
        $this->authorize('update', $role);
        $permissions = Permission::forApp()->orderBy('name')->get();

        return view('roles.edit', compact('role', 'permissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->ensureOwned($role);
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Successfully updated role.');
    }

    public function destroy(Role $role)
    {
        $this->ensureOwned($role);
        $this->authorize('delete', $role);
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Successfully deleted role.');
    }

    // super-admin dan role app lain tidak bisa diubah dari app ini
    private function ensureOwned(Role $role): void
    {
        abort_unless(str_starts_with($role->name, Role::appPrefix()), 404);
    }
}
