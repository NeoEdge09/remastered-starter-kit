import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermission() {
    const page = usePage<SharedData>();

    const permissions = computed(() => page.props.auth.permissions);
    const isSuperAdmin = computed(() => page.props.auth.isSuperAdmin);

    /**
     * Check if the current user has a specific permission.
     */
    const can = (permission: string): boolean => {
        // Super admin has all permissions
        if (isSuperAdmin.value) {
            return true;
        }

        return permissions.value.includes(permission);
    };

    /**
     * Check if the current user has any of the specified permissions.
     */
    const canAny = (permissionList: string[]): boolean => {
        if (isSuperAdmin.value) {
            return true;
        }

        return permissionList.some((permission) => permissions.value.includes(permission));
    };

    /**
     * Check if the current user has all of the specified permissions.
     */
    const canAll = (permissionList: string[]): boolean => {
        if (isSuperAdmin.value) {
            return true;
        }

        return permissionList.every((permission) => permissions.value.includes(permission));
    };

    /**
     * Check if the current user has a specific role.
     */
    const hasRole = (role: string): boolean => {
        const roles = page.props.auth.roles;
        return roles.includes(role);
    };

    /**
     * Check if the current user has any of the specified roles.
     */
    const hasAnyRole = (roleList: string[]): boolean => {
        const roles = page.props.auth.roles;
        return roleList.some((role) => roles.includes(role));
    };

    return {
        can,
        canAny,
        canAll,
        hasRole,
        hasAnyRole,
        permissions,
        isSuperAdmin,
    };
}

/**
 * Global can function for template usage.
 */
export function can(permission: string): boolean {
    const { can: checkPermission } = usePermission();
    return checkPermission(permission);
}
