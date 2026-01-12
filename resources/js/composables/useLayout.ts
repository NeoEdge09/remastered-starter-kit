import { onMounted, ref } from 'vue';

export type LayoutType = 'sidebar' | 'header';

const STORAGE_KEY = 'layout-preference';

// Global reactive state that persists across components
const layoutPreference = ref<LayoutType>('sidebar');

export function initializeLayout() {
    const saved = localStorage.getItem(STORAGE_KEY) as LayoutType | null;
    if (saved && (saved === 'sidebar' || saved === 'header')) {
        layoutPreference.value = saved;
    }
}

export function useLayout() {
    onMounted(() => {
        initializeLayout();
    });

    function updateLayout(value: LayoutType) {
        layoutPreference.value = value;
        localStorage.setItem(STORAGE_KEY, value);
        // Reload page to apply new layout
        window.location.reload();
    }

    return {
        layout: layoutPreference,
        updateLayout,
    };
}
