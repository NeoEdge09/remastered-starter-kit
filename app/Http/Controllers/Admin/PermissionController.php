<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Http\Resources\PermissionGroupResource;
use App\Http\Resources\PermissionResource;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Traits\HasTableFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    use HasTableFeatures;

    /**
     * Display a listing of the permissions.
     */
    public function index(Request $request): Response
    {
        $query = Permission::with('group');

        $query = $this->applyTableQuery($query, $request, [
            'searchColumns' => ['name'],
            'filters' => [
                'group' => 'permission_group_id',
            ],
            'sortColumns' => ['name', 'created_at'],
        ]);

        return Inertia::render('Admin/Permissions/Index', [
            'page_setting' => [
                'title' => 'Permissions',
                'subtitle' => 'Manage system permissions',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Permissions', 'href' => route('admin.permissions.index')],
                ],
            ],
            'page_data' => [
                'permissions' => PermissionResource::collection($query->paginate($this->getPerPage($request))->withQueryString()),
                'permissionGroups' => PermissionGroup::ordered()->get(),
                'filters' => $this->getTableFilters($request, ['group']),
            ],
        ]);
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Permissions/Create', [
            'page_setting' => [
                'title' => 'Create Permission',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Permissions', 'href' => route('admin.permissions.index')],
                    ['title' => 'Create', 'href' => route('admin.permissions.create')],
                ],
                'back_link' => route('admin.permissions.index'),
                'action' => route('admin.permissions.store'),
            ],
            'page_data' => [
                'permissionGroups' => PermissionGroup::ordered()->get(),
            ],
        ]);
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(StorePermissionRequest $request): RedirectResponse
    {
        Permission::create([
            'name' => $request->name,
            'description' => $request->description,
            'permission_group_id' => $request->permission_group_id,
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission): Response
    {
        return Inertia::render('Admin/Permissions/Edit', [
            'page_setting' => [
                'title' => 'Edit Permission - ' . $permission->name,
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Permissions', 'href' => route('admin.permissions.index')],
                    ['title' => $permission->name, 'href' => route('admin.permissions.edit', $permission->id)],
                    ['title' => 'Edit', 'href' => route('admin.permissions.edit', $permission->id)],
                ],
                'back_link' => route('admin.permissions.index'),
                'action' => route('admin.permissions.update', $permission->id),
            ],
            'page_data' => [
                'permission' => $permission->load('group'),
                'permissionGroups' => PermissionGroup::ordered()->get(),
            ],
        ]);
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $permission->update([
            'name' => $request->name,
            'description' => $request->description,
            'permission_group_id' => $request->permission_group_id,
        ]);

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()
            ->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
