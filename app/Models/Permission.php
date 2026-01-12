<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'guard_name',
        'permission_group_id',
        'description',
    ];

    /**
     * Get the permission group.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }

    /**
     * Scope to filter by permission group.
     */
    public function scopeInGroup($query, int $groupId)
    {
        return $query->where('permission_group_id', $groupId);
    }
}
