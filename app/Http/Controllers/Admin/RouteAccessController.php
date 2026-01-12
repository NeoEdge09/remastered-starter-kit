<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RouteAccessResource;
use App\Models\Permission;
use App\Models\RouteAccess;
use App\Services\RouteService;
use App\Traits\HasTableFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RouteAccessController extends Controller
{
    use HasTableFeatures;

    public function __construct(
        protected RouteService $routeService
    ) {}

    /**
     * Display route access listing.
     */
    public function index(Request $request): Response
    {
        $query = RouteAccess::with('permission');

        $query = $this->applyTableQuery($query, $request, [
            'searchColumns' => ['route_name', 'route_uri', 'permission_name', 'description'],
            'filters' => [
                'is_active' => ['column' => 'is_active', 'boolean' => ['true' => 'true']],
                'is_public' => ['column' => 'is_public', 'boolean' => ['true' => 'true']],
            ],
            'sortColumns' => ['route_name', 'route_uri', 'permission_name', 'is_active', 'created_at'],
            'defaultSort' => 'route_name',
            'defaultDirection' => 'asc',
        ]);

        // Get unregistered routes count (all routes)
        $unregisteredCount = count($this->routeService->getUnregisteredRoutes());

        return Inertia::render('Admin/RouteAccess/Index', [
            'page_setting' => [
                'title' => 'Route Access',
                'subtitle' => 'Manage route permissions and access control',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Route Access', 'href' => route('admin.route-accesses.index')],
                ],
            ],
            'page_data' => [
                'routeAccesses' => RouteAccessResource::collection($query->paginate($this->getPerPage($request))->withQueryString()),
                'filters' => $this->getTableFilters($request, ['is_active', 'is_public']),
                'unregisteredCount' => $unregisteredCount,
            ],
        ]);
    }

    /**
     * Show form for creating a new route access.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/RouteAccess/Create', [
            'page_setting' => [
                'title' => 'Create Route Access',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Route Access', 'href' => route('admin.route-accesses.index')],
                    ['title' => 'Create', 'href' => route('admin.route-accesses.create')],
                ],
                'back_link' => route('admin.route-accesses.index'),
                'action' => route('admin.route-accesses.store'),
            ],
            'page_data' => [
                'permissions' => Permission::orderBy('name')->get(),
                'availableRoutes' => $this->routeService->getUnregisteredRoutes(),
            ],
        ]);
    }

    /**
     * Store a new route access.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'route_name' => ['required', 'string', 'max:255', 'unique:route_accesses,route_name'],
            'route_uri' => ['nullable', 'string', 'max:255'],
            'route_method' => ['nullable', 'string', 'max:10'],
            'permission_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'is_public' => ['boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $routeAccess = RouteAccess::create($validated);

        // Link to existing Spatie permission if permission_name is provided
        if ($routeAccess->permission_name) {
            $permission = Permission::where('name', $routeAccess->permission_name)->first();
            if ($permission) {
                $routeAccess->update(['permission_id' => $permission->id]);
            }
        }

        return redirect()
            ->route('admin.route-accesses.index')
            ->with('success', 'Route access created successfully.');
    }

    /**
     * Show form for editing a route access.
     */
    public function edit(RouteAccess $routeAccess): Response
    {
        return Inertia::render('Admin/RouteAccess/Edit', [
            'page_setting' => [
                'title' => 'Edit Route Access - ' . $routeAccess->route_name,
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Route Access', 'href' => route('admin.route-accesses.index')],
                    ['title' => $routeAccess->route_name, 'href' => route('admin.route-accesses.edit', $routeAccess->id)],
                    ['title' => 'Edit', 'href' => route('admin.route-accesses.edit', $routeAccess->id)],
                ],
                'back_link' => route('admin.route-accesses.index'),
                'action' => route('admin.route-accesses.update', $routeAccess->id),
            ],
            'page_data' => [
                'routeAccess' => $routeAccess->load('permission'),
                'permissions' => Permission::orderBy('name')->get(),
            ],
        ]);
    }

    /**
     * Update a route access.
     */
    public function update(Request $request, RouteAccess $routeAccess): RedirectResponse
    {
        $validated = $request->validate([
            'route_name' => ['required', 'string', 'max:255', 'unique:route_accesses,route_name,' . $routeAccess->id],
            'route_uri' => ['nullable', 'string', 'max:255'],
            'route_method' => ['nullable', 'string', 'max:10'],
            'permission_name' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'is_public' => ['boolean'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $routeAccess->update($validated);

        // Link to existing Spatie permission if permission_name is provided
        if ($routeAccess->permission_name) {
            $permission = Permission::where('name', $routeAccess->permission_name)->first();
            $routeAccess->update(['permission_id' => $permission?->id]);
        } else {
            $routeAccess->update(['permission_id' => null]);
        }

        return redirect()
            ->route('admin.route-accesses.index')
            ->with('success', 'Route access updated successfully.');
    }

    /**
     * Delete a route access.
     */
    public function destroy(RouteAccess $routeAccess): RedirectResponse
    {
        $routeAccess->delete();

        return redirect()
            ->route('admin.route-accesses.index')
            ->with('success', 'Route access deleted successfully.');
    }

    /**
     * Scan and sync routes.
     */
    public function scan(Request $request): RedirectResponse
    {
        // Get prefixes from request, empty array = all routes
        $prefixes = $request->input('prefixes', []);

        $result = $this->routeService->syncRouteAccesses($prefixes);

        $message = sprintf(
            'Route scan completed: %d created, %d updated, %d removed, %d matched with existing permissions.',
            $result['created'],
            $result['updated'],
            $result['removed'],
            $result['matched']
        );

        return redirect()
            ->route('admin.route-accesses.index')
            ->with('success', $message);
    }

    /**
     * Bulk update route accesses.
     */
    public function bulkUpdate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:route_accesses,id'],
            'action' => ['required', 'string', 'in:activate,deactivate,make_public,make_protected'],
        ]);

        $query = RouteAccess::whereIn('id', $validated['ids']);

        switch ($validated['action']) {
            case 'activate':
                $query->update(['is_active' => true]);
                $message = 'Routes activated successfully.';
                break;
            case 'deactivate':
                $query->update(['is_active' => false]);
                $message = 'Routes deactivated successfully.';
                break;
            case 'make_public':
                $query->update(['is_public' => true]);
                $message = 'Routes made public successfully.';
                break;
            case 'make_protected':
                $query->update(['is_public' => false]);
                $message = 'Routes made protected successfully.';
                break;
            default:
                $message = 'No action performed.';
        }

        RouteAccess::clearCache();

        return redirect()
            ->route('admin.route-accesses.index')
            ->with('success', $message);
    }

    /**
     * Bulk delete route accesses.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'integer', 'exists:route_accesses,id'],
        ]);

        RouteAccess::whereIn('id', $validated['ids'])->delete();

        return redirect()
            ->route('admin.route-accesses.index')
            ->with('success', 'Route accesses deleted successfully.');
    }

    /**
     * Link all route accesses to existing permissions.
     */
    public function syncPermissions(): RedirectResponse
    {
        $routeAccesses = RouteAccess::whereNotNull('permission_name')->get();
        $linked = 0;

        foreach ($routeAccesses as $routeAccess) {
            $permission = Permission::where('name', $routeAccess->permission_name)->first();
            if ($permission && $routeAccess->permission_id !== $permission->id) {
                $routeAccess->update(['permission_id' => $permission->id]);
                $linked++;
            }
        }

        return redirect()
            ->route('admin.route-accesses.index')
            ->with('success', "Linked {$linked} route accesses to existing permissions.");
    }
}
