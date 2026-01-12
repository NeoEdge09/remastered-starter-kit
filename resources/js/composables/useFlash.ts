import type { Flash, SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

export type ToastType = 'success' | 'error' | 'warning' | 'info';

export interface Toast {
    id: string;
    type: ToastType;
    message: string;
    duration?: number;
}

const toasts = ref<Toast[]>([]);

export function useFlash() {
    const page = usePage<SharedData>();

    const flash = computed<Flash>(() => page.props.flash);

    /**
     * Add a toast notification.
     */
    const addToast = (type: ToastType, message: string, duration = 5000) => {
        const id = Math.random().toString(36).substring(2, 9);
        const toast: Toast = { id, type, message, duration };
        toasts.value.push(toast);

        if (duration > 0) {
            setTimeout(() => {
                removeToast(id);
            }, duration);
        }

        return id;
    };

    /**
     * Remove a toast by ID.
     */
    const removeToast = (id: string) => {
        const index = toasts.value.findIndex((t) => t.id === id);
        if (index > -1) {
            toasts.value.splice(index, 1);
        }
    };

    /**
     * Clear all toasts.
     */
    const clearToasts = () => {
        toasts.value = [];
    };

    /**
     * Watch for flash messages and convert to toasts.
     */
    const watchFlash = () => {
        watch(
            () => page.props.flash,
            (newFlash) => {
                if (newFlash.success) {
                    addToast('success', newFlash.success);
                }
                if (newFlash.error) {
                    addToast('error', newFlash.error);
                }
                if (newFlash.warning) {
                    addToast('warning', newFlash.warning);
                }
                if (newFlash.info) {
                    addToast('info', newFlash.info);
                }
            },
            { immediate: true },
        );
    };

    return {
        flash,
        toasts,
        addToast,
        removeToast,
        clearToasts,
        watchFlash,
    };
}
