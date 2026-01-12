<script setup lang="ts">
import { useFlash, type ToastType } from '@/composables/useFlash';
import { AlertCircle, AlertTriangle, CheckCircle, Info, X } from 'lucide-vue-next';
import { onMounted } from 'vue';

const { toasts, removeToast, watchFlash } = useFlash();

onMounted(() => {
    watchFlash();
});

const getIcon = (type: ToastType) => {
    switch (type) {
        case 'success':
            return CheckCircle;
        case 'error':
            return AlertCircle;
        case 'warning':
            return AlertTriangle;
        case 'info':
            return Info;
        default:
            return Info;
    }
};

const getClasses = (type: ToastType) => {
    switch (type) {
        case 'success':
            return 'bg-green-50 text-green-800 dark:bg-green-900/50 dark:text-green-200 border-green-200 dark:border-green-800';
        case 'error':
            return 'bg-red-50 text-red-800 dark:bg-red-900/50 dark:text-red-200 border-red-200 dark:border-red-800';
        case 'warning':
            return 'bg-yellow-50 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200 border-yellow-200 dark:border-yellow-800';
        case 'info':
            return 'bg-blue-50 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200 border-blue-200 dark:border-blue-800';
        default:
            return 'bg-gray-50 text-gray-800 dark:bg-gray-900/50 dark:text-gray-200 border-gray-200 dark:border-gray-800';
    }
};

const getIconClasses = (type: ToastType) => {
    switch (type) {
        case 'success':
            return 'text-green-500 dark:text-green-400';
        case 'error':
            return 'text-red-500 dark:text-red-400';
        case 'warning':
            return 'text-yellow-500 dark:text-yellow-400';
        case 'info':
            return 'text-blue-500 dark:text-blue-400';
        default:
            return 'text-gray-500 dark:text-gray-400';
    }
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed right-4 top-4 z-50 flex max-w-sm flex-col gap-2">
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="flex items-start gap-3 rounded-lg border p-4 shadow-lg"
                    :class="getClasses(toast.type)"
                >
                    <component :is="getIcon(toast.type)" class="h-5 w-5 flex-shrink-0" :class="getIconClasses(toast.type)" />
                    <p class="flex-1 text-sm font-medium">{{ toast.message }}</p>
                    <button @click="removeToast(toast.id)" class="flex-shrink-0 transition-opacity hover:opacity-70">
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>
