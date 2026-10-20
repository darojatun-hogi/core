<?php

namespace App\Http\Controllers;

use App\Support\ManagedApp;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserActivityNotification;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $search = $request->input('search');

        $users = User::query()
            ->with('roles')
            ->withAppAccess(ManagedApp::current())
            ->when(! $request->user()->hasRole(config('access.super_admin_role')), function ($query) {
                $query->whereDoesntHave('roles', function ($query) {
                    $query->where('name', config('access.super_admin_role'));
                });
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                   $query->where('name', 'ilike', "%{$search}%")
                       ->orWhere('email', 'ilike', "%{$search}%")
                       ->orWhere('username', 'ilike', "%{$search}%");
               });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', User::class);

        $roles = Role::forApp(ManagedApp::current())->pluck('name', 'name');

        return view('users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserStoreRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->syncAppRoles($validated['roles'], ManagedApp::current());

        $activity = $user->activities()->where('event', 'created')->latest()->first();
        if ($activity) {
            $properties = $activity->properties->toArray();
            $properties['attributes']['roles'] = $user->getRoleNames()->toArray();
            $activity->properties = collect($properties);
            $activity->save();
        }

        Notification::send(
            User::notifiableAdmins(except: Auth::user(), app: ManagedApp::current()),
            new UserActivityNotification($user, Auth::user(), 'created')
        );

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load('roles');

        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::forApp(ManagedApp::current())->pluck('name', 'name');

        return view('users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserUpdateRequest $request, User $user)
    {
        $validated = $request->validated();

        $oldRoles = $user->getRoleNames()->toArray();
        
        $user->update([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => filled($validated['password'] ?? null) ? Hash::make($validated['password']) : $user->password,
        ]);

        $user->syncAppRoles($validated['roles'], ManagedApp::current());

        $newRoles = $user->getRoleNames()->toArray();

        if ($oldRoles !== $newRoles) {
            activity()
                ->causedBy(Auth::user())
                ->performedOn($user)
                ->event('updated')
                ->withProperties(['old' => ['roles' => $oldRoles], 'attributes' => ['roles' => $newRoles]])
                ->log("Roles updated for \"{$user->name}\"");
        }

        Notification::send(
            User::notifiableAdmins(except: Auth::user(), app: ManagedApp::current()),
            new UserActivityNotification($user, Auth::user(), 'updated')
        );

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Akun dipakai app lain juga, jadi "hapus" di sini = cabut akses ke app ini, bukan hapus akun.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $oldRoles = $user->getRoleNames()->toArray();
        $user->syncAppRoles([], ManagedApp::current());

        activity()
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->event('updated')
            ->withProperties(['old' => ['roles' => $oldRoles], 'attributes' => ['roles' => $user->getRoleNames()->toArray()]])
            ->log("Akses " . ManagedApp::current() . " dicabut untuk \"{$user->name}\"");

        Notification::send(
            User::notifiableAdmins(except: Auth::user(), app: ManagedApp::current()),
            new UserActivityNotification($user, Auth::user(), 'revoked access for')
        );

        return redirect()->route('users.index')->with('success', 'Akses user ke aplikasi ini dicabut.');
    }
}
