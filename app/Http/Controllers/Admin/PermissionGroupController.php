<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionGroupRequest;
use App\Http\Requests\Admin\UpdatePermissionGroupRequest;
use App\Http\Resources\PermissionGroupResource;
use App\Models\PermissionGroup;
use App\Traits\HasTableFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PermissionGroupController extends Controller
{
    use HasTableFeatures;

    /**
     * Display a listing of the permission groups.
     */
    public function index(Request $request): Response
    {
        $query = PermissionGroup::withCount('permissions');

        $query = $this->applyTableQuery($query, $request, [
            'searchColumns' => ['name'],
            'sortColumns' => ['order', 'name', 'created_at'],
            'defaultSort' => 'order',
            'defaultDirection' => 'asc',
        ]);

        return Inertia::render('Admin/PermissionGroups/Index', [
            'page_setting' => [
                'title' => 'Permission Groups',
                'subtitle' => 'Manage permission groups and categories',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Permission Groups', 'href' => route('admin.permission-groups.index')],
                ],
            ],
            'page_data' => [
                'permissionGroups' => PermissionGroupResource::collection($query->paginate($this->getPerPage($request))->withQueryString()),
                'filters' => $this->getTableFilters($request),
            ],
        ]);
    }

    /**
     * Show the form for creating a new permission group.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/PermissionGroups/Create', [
            'page_setting' => [
                'title' => 'Create Permission Group',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Permission Groups', 'href' => route('admin.permission-groups.index')],
                    ['title' => 'Create', 'href' => route('admin.permission-groups.create')],
                ],
                'back_link' => route('admin.permission-groups.index'),
                'action' => route('admin.permission-groups.store'),
            ],
            'page_data' => [],
        ]);
    }

    /**
     * Store a newly created permission group in storage.
     */
    public function store(StorePermissionGroupRequest $request): RedirectResponse
    {
        PermissionGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'order' => $request->order ?? 0,
        ]);

        return redirect()
            ->route('admin.permission-groups.index')
            ->with('success', 'Permission group created successfully.');
    }

    /**
     * Show the form for editing the specified permission group.
     */
    public function edit(PermissionGroup $permissionGroup): Response
    {
        return Inertia::render('Admin/PermissionGroups/Edit', [
            'page_setting' => [
                'title' => 'Edit Permission Group - ' . $permissionGroup->name,
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Permission Groups', 'href' => route('admin.permission-groups.index')],
                    ['title' => $permissionGroup->name, 'href' => route('admin.permission-groups.edit', $permissionGroup->id)],
                    ['title' => 'Edit', 'href' => route('admin.permission-groups.edit', $permissionGroup->id)],
                ],
                'back_link' => route('admin.permission-groups.index'),
                'action' => route('admin.permission-groups.update', $permissionGroup->id),
            ],
            'page_data' => [
                'permissionGroup' => $permissionGroup->load('permissions'),
            ],
        ]);
    }

    /**
     * Update the specified permission group in storage.
     */
    public function update(UpdatePermissionGroupRequest $request, PermissionGroup $permissionGroup): RedirectResponse
    {
        $permissionGroup->update([
            'name' => $request->name,
            'description' => $request->description,
            'order' => $request->order ?? $permissionGroup->order,
        ]);

        return redirect()
            ->route('admin.permission-groups.index')
            ->with('success', 'Permission group updated successfully.');
    }

    /**
     * Remove the specified permission group from storage.
     */
    public function destroy(PermissionGroup $permissionGroup): RedirectResponse
    {
        // Check if group has permissions
        if ($permissionGroup->permissions()->count() > 0) {
            return back()->with('error', 'Cannot delete permission group with existing permissions.');
        }

        $permissionGroup->delete();

        return redirect()
            ->route('admin.permission-groups.index')
            ->with('success', 'Permission group deleted successfully.');
    }
}
