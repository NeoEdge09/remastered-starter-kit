<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Http\Resources\PermissionGroupResource;
use App\Http\Resources\RoleResource;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Traits\HasTableFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    use HasTableFeatures;

    /**
     * Display a listing of the roles.
     */
    public function index(Request $request): Response
    {
        $query = Role::withCount('permissions');

        $query = $this->applyTableQuery($query, $request, [
            'searchColumns' => ['name'],
            'sortColumns' => ['name', 'created_at'],
        ]);

        return Inertia::render('Admin/Roles/Index', [
            'page_setting' => [
                'title' => 'Roles',
                'subtitle' => 'Manage system roles and their permissions',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Roles', 'href' => route('admin.roles.index')],
                ],
            ],
            'page_data' => [
                'roles' => RoleResource::collection($query->paginate($this->getPerPage($request))->withQueryString()),
                'filters' => $this->getTableFilters($request),
            ],
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Roles/Create', [
            'page_setting' => [
                'title' => 'Create Role',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Roles', 'href' => route('admin.roles.index')],
                    ['title' => 'Create', 'href' => route('admin.roles.create')],
                ],
                'back_link' => route('admin.roles.index'),
                'action' => route('admin.roles.store'),
            ],
            'page_data' => [
                'permissionGroups' => PermissionGroup::with('permissions')->ordered()->get(),
            ],
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->permissions) {
            $permissions = Permission::whereIn('id', $request->permissions)->pluck('name');
            $role->syncPermissions($permissions);
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): Response
    {
        return Inertia::render('Admin/Roles/Show', [
            'role' => new RoleResource($role->load('permissions')),
        ]);
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): Response
    {
        return Inertia::render('Admin/Roles/Edit', [
            'page_setting' => [
                'title' => 'Edit Role - ' . $role->name,
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Roles', 'href' => route('admin.roles.index')],
                    ['title' => $role->name, 'href' => route('admin.roles.edit', $role->id)],
                    ['title' => 'Edit', 'href' => route('admin.roles.edit', $role->id)],
                ],
                'back_link' => route('admin.roles.index'),
                'action' => route('admin.roles.update', $role->id),
            ],
            'page_data' => [
                'role' => $role->load('permissions'),
                'permissionGroups' => PermissionGroup::with('permissions')->ordered()->get(),
                'rolePermissions' => $role->permissions->pluck('id'),
            ],
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        // Prevent modifying super-admin role name
        if ($role->name === 'super-admin' && $request->name !== 'super-admin') {
            return back()->with('error', 'Cannot modify the super-admin role name.');
        }

        $role->update([
            'name' => $request->name,
        ]);

        if ($request->has('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->pluck('name');
            $role->syncPermissions($permissions);
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        // Prevent deleting super-admin role
        if ($role->name === 'super-admin') {
            return back()->with('error', 'Cannot delete the super-admin role.');
        }

        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
