<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;

class Role extends SpatieRole
{
    use LogsActivity;

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