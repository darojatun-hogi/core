<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Models\Activity;

class Permission extends SpatiePermission
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
            'created' => "Permission \"{$this->name}\" was created",
            'deleted' => "Permission \"{$this->name}\" was deleted",
            default   => $activity->description,
        };
    }
}