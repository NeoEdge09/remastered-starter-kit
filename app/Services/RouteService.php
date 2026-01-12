<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\RouteAccess;
use Illuminate\Support\Facades\Route;

class RouteService
{
    /**
     * Get all named routes that are suitable for menu items.
     * Excludes API routes, generated routes, and internal Laravel routes.
     *
     * @return array<int, array{name: string, uri: string, methods: array}>
     */
    public function getNamedRoutes(): array
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(function ($route) {
                // Must have a name
                if (empty($route->getName())) {
                    return false;
                }

                $name = $route->getName();

                // Exclude API routes
                if (str_starts_with($name, 'api.')) {
                    return false;
                }

                // Exclude internal Laravel routes
                if (str_starts_with($name, 'sanctum.')) {
                    return false;
                }

                if (str_starts_with($name, 'ignition.')) {
                    return false;
                }

                if (str_starts_with($name, 'livewire.')) {
                    return false;
                }

                // Exclude generated routes (like storage links)
                if (str_starts_with($name, 'generated::')) {
                    return false;
                }

                // Only include GET routes (menus usually link to GET endpoints)
                if (! in_array('GET', $route->methods())) {
                    return false;
                }

                return true;
            })
            ->map(function ($route) {
                return [
                    'name' => $route->getName(),
                    'uri' => '/' . ltrim($route->uri(), '/'),
                    'methods' => $route->methods(),
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();

        return $routes;
    }

    /**
     * Get routes formatted for select dropdown.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function getRoutesForSelect(): array
    {
        return collect($this->getNamedRoutes())
            ->map(function ($route) {
                return [
                    'value' => $route['name'],
                    'label' => $route['name'] . ' (' . $route['uri'] . ')',
                ];
            })
            ->all();
    }

    /**
     * Get all named routes for route access management.
     * Includes all HTTP methods, excludes internal routes.
     *
     * @param  array  $prefixes  Filter by route name prefixes
     * @return array<int, array{name: string, uri: string, method: string}>
     */
    public function getAllNamedRoutes(array $prefixes = []): array
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(function ($route) use ($prefixes) {
                // Must have a name
                if (empty($route->getName())) {
                    return false;
                }

                $name = $route->getName();

                // Exclude internal Laravel/package routes
                $excludePrefixes = [
                    'sanctum.',
                    'ignition.',
                    'livewire.',
                    'generated::',
                    'debugbar.',
                    'telescope.',
                    'horizon.',
                    'pulse.',
                ];
                foreach ($excludePrefixes as $prefix) {
                    if (str_starts_with($name, $prefix)) {
                        return false;
                    }
                }

                // Exclude Laravel default auth routes
                $excludeRoutes = [
                    'login',
                    'logout',
                    'register',
                    'password.edit',
                    'password.store',
                    'password.request',
                    'password.reset',
                    'password.email',
                    'password.update',
                    'password.confirm',
                    'verification.notice',
                    'verification.verify',
                    'verification.send',
                    'profile.edit',
                    'profile.update',
                    'profile.destroy',
                    'storage.local'
                ];
                if (in_array($name, $excludeRoutes)) {
                    return false;
                }

                // Exclude settings routes (profile/password/appearance)
                if (str_starts_with($name, 'settings.')) {
                    return false;
                }

                // Filter by prefixes if provided
                if (! empty($prefixes)) {
                    $matchesPrefix = false;
                    foreach ($prefixes as $prefix) {
                        if (str_starts_with($name, $prefix)) {
                            $matchesPrefix = true;
                            break;
                        }
                    }

                    return $matchesPrefix;
                }

                return true;
            })
            ->flatMap(function ($route) {
                // Create an entry for each HTTP method
                $methods = array_filter($route->methods(), fn($m) => $m !== 'HEAD');

                return collect($methods)->map(fn($method) => [
                    'name' => $route->getName(),
                    'uri' => '/' . ltrim($route->uri(), '/'),
                    'method' => $method,
                ]);
            })
            ->unique(fn($route) => $route['name'] . '-' . $route['method'])
            ->sortBy('name')
            ->values()
            ->all();

        return $routes;
    }

    /**
     * Scan and sync routes to route_accesses table.
     *
     * @param  array  $prefixes  Filter by route name prefixes. Empty = all routes.
     * @return array{created: int, updated: int, removed: int, matched: int}
     */
    public function syncRouteAccesses(array $prefixes = []): array
    {
        $routes = $this->getAllNamedRoutes($prefixes);
        $existingRoutes = RouteAccess::pluck('route_name')->toArray();
        $scannedRouteNames = [];

        // Get all existing Spatie permissions for matching (name => id)
        $existingPermissions = Permission::pluck('id', 'name')->toArray();

        $created = 0;
        $updated = 0;
        $matched = 0;

        foreach ($routes as $route) {
            // Only use the first method (primary)
            if (in_array($route['name'], $scannedRouteNames)) {
                continue;
            }

            $scannedRouteNames[] = $route['name'];

            // Try to find a matching existing permission
            $suggestedPermission = RouteAccess::generatePermissionName($route['name']);
            $permissionName = isset($existingPermissions[$suggestedPermission])
                ? $suggestedPermission
                : null;
            $permissionId = $permissionName
                ? $existingPermissions[$suggestedPermission]
                : null;

            if ($permissionName) {
                $matched++;
            }

            $routeAccess = RouteAccess::updateOrCreate(
                ['route_name' => $route['name']],
                [
                    'route_uri' => $route['uri'],
                    'route_method' => $route['method'],
                    'permission_name' => $permissionName,
                    'permission_id' => $permissionId,
                ]
            );

            if ($routeAccess->wasRecentlyCreated) {
                $created++;
            } elseif ($routeAccess->wasChanged()) {
                $updated++;
            }
        }

        // Remove routes that no longer exist (only if prefixes are specified)
        $removed = 0;
        if (! empty($prefixes)) {
            $removed = RouteAccess::whereNotIn('route_name', $scannedRouteNames)
                ->whereIn('route_name', function ($query) use ($prefixes) {
                    $query->select('route_name')
                        ->from('route_accesses');

                    foreach ($prefixes as $index => $prefix) {
                        if ($index === 0) {
                            $query->where('route_name', 'like', $prefix . '%');
                        } else {
                            $query->orWhere('route_name', 'like', $prefix . '%');
                        }
                    }
                })
                ->delete();
        }

        // Clear cache
        RouteAccess::clearCache();

        return [
            'created' => $created,
            'updated' => $updated,
            'removed' => $removed,
            'matched' => $matched,
        ];
    }

    /**
     * Get unregistered routes (routes not in route_accesses table).
     *
     * @param  array  $prefixes  Filter by route name prefixes. Empty = all routes.
     */
    public function getUnregisteredRoutes(array $prefixes = []): array
    {
        $routes = $this->getAllNamedRoutes($prefixes);
        $existingRoutes = RouteAccess::pluck('route_name')->toArray();

        return collect($routes)
            ->filter(fn($route) => ! in_array($route['name'], $existingRoutes))
            ->unique('name')
            ->values()
            ->all();
    }
}
