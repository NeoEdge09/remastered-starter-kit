<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMenuRequest;
use App\Http\Requests\Admin\UpdateMenuRequest;
use App\Models\Menu;
use App\Models\Permission;
use App\Services\MenuService;
use App\Services\RouteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function __construct(
        protected MenuService $menuService,
        protected RouteService $routeService
    ) {}

    /**
     * Display a listing of the menus.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Admin/Menus/Index', [
            'page_setting' => [
                'title' => 'Menus',
                'subtitle' => 'Manage navigation menus',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Menus', 'href' => route('admin.menus.index')],
                ],
            ],
            'page_data' => [
                'menus' => $this->menuService->getAllMenuTree(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new menu.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Menus/Create', [
            'page_setting' => [
                'title' => 'Create Menu',
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Menus', 'href' => route('admin.menus.index')],
                    ['title' => 'Create', 'href' => route('admin.menus.create')],
                ],
                'back_link' => route('admin.menus.index'),
                'action' => route('admin.menus.store'),
            ],
            'page_data' => [
                'menus' => $this->menuService->getAllMenuTree(),
                'permissions' => Permission::orderBy('name')->get(),
                'routes' => $this->routeService->getRoutesForSelect(),
            ],
        ]);
    }

    /**
     * Store a newly created menu in storage.
     */
    public function store(StoreMenuRequest $request): RedirectResponse
    {
        Menu::create([
            'name' => $request->name,
            'route_name' => $request->route_name,
            'url' => $request->url,
            'icon' => $request->icon,
            'parent_id' => $request->parent_id,
            'order' => $request->order ?? 0,
            'permission_name' => $request->permission_name,
            'is_active' => $request->is_active ?? true,
        ]);

        $this->menuService->clearCache();

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu created successfully.');
    }

    /**
     * Show the form for editing the specified menu.
     */
    public function edit(Menu $menu): Response
    {
        return Inertia::render('Admin/Menus/Edit', [
            'page_setting' => [
                'title' => 'Edit Menu - ' . $menu->name,
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'href' => route('dashboard')],
                    ['title' => 'Menus', 'href' => route('admin.menus.index')],
                    ['title' => $menu->name, 'href' => route('admin.menus.edit', $menu->id)],
                    ['title' => 'Edit', 'href' => route('admin.menus.edit', $menu->id)],
                ],
                'back_link' => route('admin.menus.index'),
                'action' => route('admin.menus.update', $menu->id),
            ],
            'page_data' => [
                'menu' => $menu->load(['parent', 'children']),
                'menus' => $this->menuService->getAllMenuTree(),
                'permissions' => Permission::orderBy('name')->get(),
                'routes' => $this->routeService->getRoutesForSelect(),
            ],
        ]);
    }

    /**
     * Update the specified menu in storage.
     */
    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $menu->update([
            'name' => $request->name,
            'route_name' => $request->route_name,
            'url' => $request->url,
            'icon' => $request->icon,
            'parent_id' => $request->parent_id,
            'order' => $request->order ?? $menu->order,
            'permission_name' => $request->permission_name,
            'is_active' => $request->is_active ?? $menu->is_active,
        ]);

        $this->menuService->clearCache();

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu updated successfully.');
    }

    /**
     * Remove the specified menu from storage.
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        // Children will be deleted via cascade
        $menu->delete();

        $this->menuService->clearCache();

        return redirect()
            ->route('admin.menus.index')
            ->with('success', 'Menu deleted successfully.');
    }

    /**
     * Reorder menus.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'menus' => ['required', 'array'],
            'menus.*.id' => ['required', 'exists:menus,id'],
            'menus.*.parent_id' => ['nullable', 'exists:menus,id'],
            'menus.*.order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->menus as $menuData) {
            Menu::where('id', $menuData['id'])->update([
                'parent_id' => $menuData['parent_id'],
                'order' => $menuData['order'],
            ]);
        }

        $this->menuService->clearCache();

        return back()->with('success', 'Menu order updated successfully.');
    }
}
