<?php

namespace App\Http\Middleware;

use App\Models\RouteAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AutoPermission
{
    /**
     * Handle an incoming request.
     *
     * Simplified middleware that only checks route_accesses table.
     * All routes must be scanned first via Admin > Route Access > Scan Routes.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Guest users are not allowed
        if (! $user) {
            abort(403, 'Unauthorized');
        }

        // Super admin bypasses all permission checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user is active
        if (! $user->isActive()) {
            Auth::guard('web')->logout();
            abort(403, 'Your account has been deactivated.');
        }

        $routeName = $request->route()?->getName();

        // If no route name, allow access
        if (! $routeName) {
            return $next($request);
        }

        // Get route access from database (cached)
        $routeAccess = $this->getRouteAccess($routeName);

        // Route not registered in DB = allow access (or you can change to 403)
        if (! $routeAccess) {
            return $next($request);
        }

        // Route is inactive = 403
        if (! $routeAccess['is_active']) {
            abort(403, 'This route is currently disabled.');
        }

        // Route is public = allow access
        if ($routeAccess['is_public']) {
            return $next($request);
        }

        // No permission required = allow access
        if (empty($routeAccess['permission_name'])) {
            return $next($request);
        }

        // Check if user has the required permission
        if (! $user->hasPermissionTo($routeAccess['permission_name'])) {
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }

    /**
     * Get route access data from cache.
     */
    protected function getRouteAccess(string $routeName): ?array
    {
        $routes = RouteAccess::getCachedRoutes();

        return $routes[$routeName] ?? null;
    }
}
