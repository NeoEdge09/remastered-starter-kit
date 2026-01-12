<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class RouteAccess extends Model
{
    use LogsActivity;

    protected $fillable = [
        'route_name',
        'route_uri',
        'route_method',
        'permission_name',
        'permission_id',
        'is_active',
        'is_public',
        'description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * Cache key for route access permissions.
     */
    public const CACHE_KEY = 'route_access_permissions';

    /**
     * Cache TTL in seconds (5 minutes).
     */
    public const CACHE_TTL = 300;

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Clear cache when route access is created, updated, or deleted
        static::saved(function () {
            static::clearCache();
        });

        static::deleted(function () {
            static::clearCache();
        });
    }

    /**
     * Get the permission for this route access.
     */
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    /**
     * Scope for active route accesses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for public routes (no permission needed).
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for protected routes (permission needed).
     */
    public function scopeProtected($query)
    {
        return $query->where('is_public', false);
    }

    /**
     * Get cached route permissions mapping.
     */
    public static function getCachedPermissions(): array
    {
        return Cache::remember(static::CACHE_KEY, static::CACHE_TTL, function () {
            return static::active()
                ->whereNotNull('permission_name')
                ->pluck('permission_name', 'route_name')
                ->toArray();
        });
    }

    /**
     * Get cached public routes.
     */
    public static function getCachedPublicRoutes(): array
    {
        return Cache::remember(static::CACHE_KEY . '_public', static::CACHE_TTL, function () {
            return static::active()
                ->public()
                ->pluck('route_name')
                ->toArray();
        });
    }

    /**
     * Get all cached routes with their full data.
     */
    public static function getCachedRoutes(): array
    {
        return Cache::remember(static::CACHE_KEY . '_routes', static::CACHE_TTL, function () {
            return static::query()
                ->get(['route_name', 'permission_name', 'is_active', 'is_public'])
                ->keyBy('route_name')
                ->map(fn($item) => [
                    'permission_name' => $item->permission_name,
                    'is_active' => $item->is_active,
                    'is_public' => $item->is_public,
                ])
                ->toArray();
        });
    }

    /**
     * Clear the route access cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
        Cache::forget(static::CACHE_KEY . '_public');
        Cache::forget(static::CACHE_KEY . '_routes');
    }

    /**
     * Link to existing permission by name.
     * Does NOT create new permissions - only links to existing ones.
     */
    public function linkPermission(): void
    {
        if (! $this->permission_name) {
            $this->permission_id = null;
            $this->save();

            return;
        }

        $permission = Permission::where('name', $this->permission_name)->first();
        $this->permission_id = $permission?->id;
        $this->save();
    }

    /**
     * Generate permission name from route name.
     * 
     * Auto-detects prefix and extracts resource.action format.
     * Works with any prefix: admin, api, merchant, vendor, customer, etc.
     *
     * Examples:
     * - admin.users.index → user.view
     * - merchant.products.create → product.create
     * - api.v1.orders.store → order.create
     * - customer.profile.update → profile.update
     * - dashboard → dashboard (no action, keep as-is)
     */
    public static function generatePermissionName(string $routeName): string
    {
        $parts = explode('.', $routeName);

        // Single part route (e.g., 'dashboard') - return as-is
        if (count($parts) === 1) {
            return $routeName;
        }

        // Two parts (e.g., 'users.index') - resource.action
        if (count($parts) === 2) {
            return self::formatPermission($parts[0], $parts[1]);
        }

        // Three or more parts - remove prefix(es), take last two meaningful parts
        // e.g., 'admin.users.index' → 'users.index'
        // e.g., 'api.v1.orders.store' → 'orders.store'

        // The action is always the last part
        $action = array_pop($parts);

        // The resource is the second-to-last part (before action)
        $resource = array_pop($parts);

        return self::formatPermission($resource, $action);
    }

    /**
     * Format resource and action into permission name.
     */
    protected static function formatPermission(string $resource, string $action): string
    {
        // Map actions to permission actions
        $actionMap = [
            // CRUD
            'index' => 'view',
            'show' => 'view',
            'create' => 'create',
            'store' => 'create',
            'edit' => 'edit',
            'update' => 'edit',
            'destroy' => 'delete',
            // Bulk
            'bulkDestroy' => 'delete',
            'bulk-destroy' => 'delete',
            'bulkUpdate' => 'edit',
            'bulk-update' => 'edit',
            // Export/Import
            'export' => 'export',
            'import' => 'import',
            'download' => 'export',
            // Status
            'activate' => 'edit',
            'deactivate' => 'edit',
            'toggle' => 'edit',
            'restore' => 'restore',
            // Ordering
            'reorder' => 'edit',
            'sort' => 'edit',
            'move' => 'edit',
            // Sync
            'sync' => 'edit',
            'sync-permissions' => 'edit',
            'syncPermissions' => 'edit',
            // Special
            'scan' => 'create',
            'clear' => 'delete',
        ];

        // Map action or use as-is (kebab-case)
        $action = $actionMap[$action] ?? \Illuminate\Support\Str::kebab($action);

        // Singularize resource (users → user, activity-logs → activity-log)
        $resource = \Illuminate\Support\Str::singular($resource);

        return $resource . '.' . $action;
    }
}
