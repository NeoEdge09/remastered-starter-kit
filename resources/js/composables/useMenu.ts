import type { MenuItem, SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useMenu() {
    const page = usePage<SharedData>();

    const menus = computed<MenuItem[]>(() => page.props.menus || []);

    /**
     * Check if a menu item is currently active based on route.
     */
    const isActive = (menu: MenuItem): boolean => {
        if (!menu.route_name) {
            return false;
        }

        const currentUrl = page.url;

        // Check if the current URL matches the menu's route
        try {
            const menuUrl = menu.route_name ? route(menu.route_name) : menu.url;
            if (menuUrl) {
                const menuPath = new URL(menuUrl, window.location.origin).pathname;
                const currentPath = new URL(currentUrl, window.location.origin).pathname;
                return currentPath.startsWith(menuPath);
            }
        } catch {
            // Route might not exist
        }

        return false;
    };

    /**
     * Check if any child of a menu is active.
     */
    const hasActiveChild = (menu: MenuItem): boolean => {
        if (!menu.children || menu.children.length === 0) {
            return false;
        }

        return menu.children.some((child) => isActive(child) || hasActiveChild(child));
    };

    /**
     * Get the URL for a menu item.
     */
    const getMenuUrl = (menu: MenuItem): string => {
        if (menu.route_name) {
            try {
                return route(menu.route_name);
            } catch {
                return '#';
            }
        }

        return menu.url || '#';
    };

    /**
     * Flatten the menu tree for searching.
     */
    const flattenMenus = (items: MenuItem[]): MenuItem[] => {
        const result: MenuItem[] = [];

        const traverse = (menuItems: MenuItem[]) => {
            for (const item of menuItems) {
                result.push(item);
                if (item.children && item.children.length > 0) {
                    traverse(item.children);
                }
            }
        };

        traverse(items);
        return result;
    };

    const flatMenus = computed(() => flattenMenus(menus.value));

    return {
        menus,
        flatMenus,
        isActive,
        hasActiveChild,
        getMenuUrl,
    };
}
