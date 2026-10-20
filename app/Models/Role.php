<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;

class Role extends SpatieRole
{
    use LogsActivity;

    // Data role & permission terpusat di Supabase
    protected $connection = 'supabase';

    // Role milik app ini (awalan "<app>."), atau app lain yang dikelola dari hub
    public function scopeForApp(Builder $query, ?string $app = null): Builder
    {
        return $query->where('name', 'like', static::appPrefix($app).'%');
    }

    public static function appPrefix(?string $app = null): string
    {
        return ($app ?? config('access.app')).'.';
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
            'created' => "Role \"{$this->name}\" was created",
            'updated' => "Role \"{$this->name}\" was updated",
            'deleted' => "Role \"{$this->name}\" was deleted",
            default   => $activity->description,
        };
    }
}
