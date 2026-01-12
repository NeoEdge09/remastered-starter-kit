import { onMounted, ref } from 'vue';

export type ThemeColor = 'default' | 'ocean' | 'forest' | 'sunset' | 'lavender' | 'rose' | 'amber' | 'emerald' | 'slate';

export interface ThemeColorOption {
    name: ThemeColor;
    label: string;
    preview: {
        light: string;
        dark: string;
    };
}

export const themeColors: ThemeColorOption[] = [
    {
        name: 'default',
        label: 'Default',
        preview: { light: '#0a0a0a', dark: '#fafafa' },
    },
    {
        name: 'ocean',
        label: 'Ocean',
        preview: { light: '#0ea5e9', dark: '#38bdf8' },
    },
    {
        name: 'forest',
        label: 'Forest',
        preview: { light: '#22c55e', dark: '#4ade80' },
    },
    {
        name: 'sunset',
        label: 'Sunset',
        preview: { light: '#f97316', dark: '#fb923c' },
    },
    {
        name: 'lavender',
        label: 'Lavender',
        preview: { light: '#8b5cf6', dark: '#a78bfa' },
    },
    {
        name: 'rose',
        label: 'Rose',
        preview: { light: '#f43f5e', dark: '#fb7185' },
    },
    {
        name: 'amber',
        label: 'Amber',
        preview: { light: '#f59e0b', dark: '#fbbf24' },
    },
    {
        name: 'emerald',
        label: 'Emerald',
        preview: { light: '#10b981', dark: '#34d399' },
    },
    {
        name: 'slate',
        label: 'Slate',
        preview: { light: '#64748b', dark: '#94a3b8' },
    },
];

export function applyThemeColor(color: ThemeColor) {
    // Remove all theme color classes
    themeColors.forEach((theme) => {
        document.documentElement.classList.remove(`theme-${theme.name}`);
    });

    // Apply new theme color class (default doesn't need a class)
    if (color !== 'default') {
        document.documentElement.classList.add(`theme-${color}`);
    }
}

export function initializeThemeColor() {
    const savedColor = localStorage.getItem('theme-color') as ThemeColor | null;
    applyThemeColor(savedColor || 'default');
}

export function useThemeColor() {
    const themeColor = ref<ThemeColor>('default');

    onMounted(() => {
        initializeThemeColor();

        const savedColor = localStorage.getItem('theme-color') as ThemeColor | null;
        if (savedColor) {
            themeColor.value = savedColor;
        }
    });

    function updateThemeColor(color: ThemeColor) {
        themeColor.value = color;
        localStorage.setItem('theme-color', color);
        applyThemeColor(color);
    }

    return {
        themeColor,
        themeColors,
        updateThemeColor,
    };
}
