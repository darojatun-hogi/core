<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;

class Permission extends SpatiePermission
{
    use LogsActivity;

    // Data role & permission terpusat di Supabase
    protected $connection = 'supabase';

    // Permission milik app ini ditambah permission identitas yang dipakai bersama
    public function scopeForApp(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('name', 'like', config('access.app').'.%')
            ->orWhereIn('name', config('access.shared_permissions')));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $activity->description = match ($eventName) {
            'created' => "Permission \"{$this->name}\" was created",
            'deleted' => "Permission \"{$this->name}\" was deleted",
            default   => $activity->description,
        };
    }
}
