<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity as SpatieLogsActivity;

trait LogsActivity
{
    use SpatieLogsActivity;

    /**
     * Get the activity log options for the model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getLogName())
            ->setDescriptionForEvent(fn(string $eventName) => $this->getActivityDescription($eventName));
    }

    /**
     * Get the log name for this model.
     */
    protected function getLogName(): string
    {
        return strtolower(class_basename($this));
    }

    /**
     * Get a human-readable description for the activity.
     */
    protected function getActivityDescription(string $eventName): string
    {
        $modelName = class_basename($this);
        $identifier = $this->getActivityIdentifier();

        return match ($eventName) {
            'created' => "{$modelName} \"{$identifier}\" was created",
            'updated' => "{$modelName} \"{$identifier}\" was updated",
            'deleted' => "{$modelName} \"{$identifier}\" was deleted",
            default => "{$modelName} \"{$identifier}\" was {$eventName}",
        };
    }

    /**
     * Get the identifier to use in activity descriptions.
     */
    protected function getActivityIdentifier(): string
    {
        return $this->name ?? $this->title ?? $this->id ?? 'Unknown';
    }
}
