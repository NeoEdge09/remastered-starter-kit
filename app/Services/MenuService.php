<?php

namespace App\Services;

use App\Models\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    /**
     * Get all menus as a nested tree structure (active only).
     */
    public function getMenuTree(): Collection
    {
        return Menu::with('allChildren')
            ->root()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Get all menus including inactive (for admin management).
     * Supports unlimited nesting levels.
     */
    public function getAllMenuTree(): Collection
    {
        $menus = Menu::with('childrenAll')
            ->whereNull('parent_id')
            ->ordered()
            ->get();

        // Transform to use 'children' key for frontend compatibility
        return $this->transformChildrenKey($menus);
    }

    /**
     * Recursively transform 'childrenAll' to 'children' key.
     */
    protected function transformChildrenKey(Collection $menus): Collection
    {
        return $menus->map(function ($menu) {
            if ($menu->relationLoaded('childrenAll')) {
                $children = $this->transformChildrenKey($menu->childrenAll);
                $menu->unsetRelation('childrenAll');
                $menu->setRelation('children', $children);
            }
            return $menu;
        });
    }

    /**
     * Get menus filtered by user permissions.
     */
    public function getMenusForUser($user): Collection
    {
        if (! $user) {
            return collect();
        }

        // Super admin sees all menus
        if ($user->isSuperAdmin()) {
            return $this->getMenuTree();
        }

        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        return $this->filterMenusByPermissions($this->getMenuTree(), $userPermissions);
    }

    /**
     * Filter menus recursively by permissions.
     */
    protected function filterMenusByPermissions(Collection $menus, array $permissions): Collection
    {
        return $menus->filter(function ($menu) use ($permissions) {
            // If no permission required, show the menu
            if (empty($menu->permission_name)) {
                return true;
            }

            return in_array($menu->permission_name, $permissions);
        })->map(function ($menu) use ($permissions) {
            if ($menu->children && $menu->children->isNotEmpty()) {
                $menu->setRelation(
                    'children',
                    $this->filterMenusByPermissions($menu->children, $permissions)
                );
            }

            return $menu;
        })->filter(function ($menu) {
            // Keep menu if it has no permission requirement, has permission, or has visible children
            return empty($menu->permission_name) ||
                ($menu->children && $menu->children->isNotEmpty()) ||
                $menu->route_name ||
                $menu->url;
        });
    }

    /**
     * Transform menus to frontend format.
     */
    public function transformForFrontend(Collection $menus): array
    {
        return $menus->map(function ($menu) {
            $item = [
                'id' => $menu->id,
                'name' => $menu->name,
                'icon' => $menu->icon,
                'route_name' => $menu->route_name,
                'url' => $menu->getRawOriginal('url'),
                'order' => $menu->order,
            ];

            if ($menu->children && $menu->children->isNotEmpty()) {
                $item['children'] = $this->transformForFrontend($menu->children);
            }

            return $item;
        })->values()->toArray();
    }

    /**
     * Clear menu cache.
     */
    public function clearCache(): void
    {
        Cache::forget('menu_permissions');
    }
}
