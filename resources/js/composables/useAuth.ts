import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useAuth() {
    const page = usePage<SharedData>();

    const user = computed(() => page.props.auth.user);
    const roles = computed(() => page.props.auth.roles);
    const permissions = computed(() => page.props.auth.permissions);
    const isSuperAdmin = computed(() => page.props.auth.isSuperAdmin);
    const isAuthenticated = computed(() => !!page.props.auth.user);

    return {
        user,
        roles,
        permissions,
        isSuperAdmin,
        isAuthenticated,
    };
}
