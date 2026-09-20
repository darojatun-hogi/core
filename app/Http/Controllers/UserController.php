<?php

namespace App\Http\Controllers;

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
    protected function notifiableAdmins(?User $except = null)
    {
        return User::role(['Admin', 'Super Admin'])
            ->when($except, fn ($q) => $q->whereKeyNot($except->id))
            ->get();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $search = $request->input('search');

        $users = User::query()
            ->with('roles')
            ->when(! $request->user()->hasRole('Super Admin'), function ($query) {
                $query->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'Super Admin');
                });
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                   $query->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('username', 'like', "%{$search}%");
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

        $roles = Role::pluck('name', 'name');

        if (! $request->user()->hasRole('Super Admin')) {
            $roles = $roles->except('Super Admin');
        }

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

        $user->syncRoles($validated['roles']);

        $activity = $user->activities()->where('event', 'created')->latest()->first();
        if ($activity) {
            $properties = $activity->properties->toArray();
            $properties['attributes']['roles'] = $user->getRoleNames()->toArray();
            $activity->properties = collect($properties);
            $activity->save();
        }

        Notification::send(
            User::notifiableAdmins(except: Auth::user()),
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

        $roles = Role::pluck('name', 'name');

        if (! $request->user()->hasRole('Super Admin')) {
            $roles = $roles->except('Super Admin');
        }

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
            'password' => $validated['password'] ? Hash::make($validated['password']) : $user->password,
        ]);

        $user->syncRoles($validated['roles']);

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
            User::notifiableAdmins(except: Auth::user()),
            new UserActivityNotification($user, Auth::user(), 'updated')
        );

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        Notification::send(
            User::notifiableAdmins(except: Auth::user()),
            new UserActivityNotification($user, Auth::user(), 'deleted')
        );

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
