<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, LogsActivity;

    // Data auth terpusat di Supabase, data bisnis app tetap di MySQL
    protected $connection = 'supabase';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            if ($user->roles()->count() === 0) {
                $user->assignRole(Role::appPrefix() . 'user');
            }
        });
    }

    public static function notifiableAdmins(?User $except = null)
    {
        return static::role([Role::appPrefix() . 'admin', config('access.super_admin_role')])
            ->when($except, fn ($q) => $q->whereKeyNot($except->id))
            ->get();
    }

    // Notifikasi terpusat di Supabase: hanya tampilkan milik app ini
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')
            ->where('app', config('access.app'))
            ->latest();
    }

    // User yang punya akses ke app ini: role "<app>.*", super-admin, atau permission "<app>.access" langsung
    public function scopeWithAppAccess(Builder $query): Builder
    {
        $app = config('access.app');

        return $query->where(fn (Builder $q) => $q
            ->whereHas('roles', fn (Builder $r) => $r->where(fn (Builder $x) => $x
                ->where('name', 'like', $app.'.%')
                ->orWhere('name', config('access.super_admin_role'))))
            ->orWhereHas('permissions', fn (Builder $p) => $p->where('name', $app.'.access')));
    }

    // Ganti role milik app ini saja; role app lain (mis. money.*) dan super-admin tetap
    public function syncAppRoles(array $roles): void
    {
        $others = $this->roles->pluck('name')
            ->reject(fn (string $name) => str_starts_with($name, Role::appPrefix()))
            ->all();

        $this->syncRoles([...$others, ...$roles]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'username', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = match ($eventName) {
            'created' => "User \"{$this->name}\" was created",
            'updated' => "User \"{$this->name}\" was updated",
            'deleted' => "User \"{$this->name}\" was deleted",
            default   => $activity->description,
        };
    }
}
