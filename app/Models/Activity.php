<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    /**
     * Get the formatted event name.
     */
    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'created' => 'Created',
            'updated' => 'Updated',
            'deleted' => 'Deleted',
            'login' => 'Login',
            'logout' => 'Logout',
            'login_failed' => 'Login Failed',
            'password_reset' => 'Password Reset',
            'email_verified' => 'Email Verified',
            default => ucfirst($this->event ?? 'Unknown'),
        };
    }

    /**
     * Get the event color for styling.
     */
    public function getEventColorAttribute(): string
    {
        return match ($this->event) {
            'created' => 'green',
            'updated' => 'blue',
            'deleted' => 'red',
            'login' => 'emerald',
            'logout' => 'orange',
            'login_failed' => 'red',
            'password_reset' => 'purple',
            'email_verified' => 'teal',
            default => 'gray',
        };
    }

    /**
     * Get the model type in a friendly format.
     */
    public function getSubjectTypeNameAttribute(): string
    {
        if (!$this->subject_type) {
            return 'Unknown';
        }

        return class_basename($this->subject_type);
    }

    /**
     * Scope to filter by log name.
     */
    public function scopeForLog(Builder $query, string $logName): Builder
    {
        return $query->where('log_name', $logName);
    }

    /**
     * Scope to filter by event.
     */
    public function scopeForEvent(Builder $query, string $event): Builder
    {
        return $query->where('event', $event);
    }

    /**
     * Scope to filter by causer.
     */
    public function scopeByCauser(Builder $query, $causer): Builder
    {
        return $query->where('causer_type', get_class($causer))
            ->where('causer_id', $causer->id);
    }

    /**
     * Scope to filter by subject type.
     */
    public function scopeForSubjectType(Builder $query, string $subjectType): Builder
    {
        return $query->where('subject_type', $subjectType);
    }

    /**
     * Scope to filter activities from today.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope to filter activities from this week.
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope to filter activities from this month.
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }
}
