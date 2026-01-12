<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'guard_name',
    ];

    /**
     * Check if this role is the super admin role.
     */
    public function isSuperAdmin(): bool
    {
        return $this->name === 'super-admin';
    }
}
