<script setup lang="ts">
import { themeColors, useThemeColor, type ThemeColor } from '@/composables/useThemeColor';
import { Check } from 'lucide-vue-next';
import { computed } from 'vue';

const { themeColor, updateThemeColor } = useThemeColor();

const isDark = computed(() => {
    if (typeof window === 'undefined') return false;
    return document.documentElement.classList.contains('dark');
});

function getPreviewColor(theme: (typeof themeColors)[0]) {
    return isDark.value ? theme.preview.dark : theme.preview.light;
}

function selectTheme(color: ThemeColor) {
    updateThemeColor(color);
}
</script>

<template>
    <div class="space-y-4">
        <div class="grid grid-cols-3 gap-3 sm:grid-cols-5 lg:grid-cols-9">
            <button
                v-for="theme in themeColors"
                :key="theme.name"
                @click="selectTheme(theme.name)"
                class="group relative flex flex-col items-center gap-2 rounded-xl border-2 p-3 transition-all duration-200 hover:scale-105"
                :class="[
                    themeColor === theme.name
                        ? 'border-primary bg-accent shadow-md'
                        : 'border-border bg-card hover:border-muted-foreground/50 hover:bg-accent/50',
                ]"
            >
                <!-- Color preview circle -->
                <div
                    class="relative h-10 w-10 rounded-full shadow-inner ring-2 ring-black/5 transition-transform duration-200 group-hover:scale-110 dark:ring-white/10"
                    :style="{ backgroundColor: getPreviewColor(theme) }"
                >
                    <!-- Check mark for selected -->
                    <div
                        v-if="themeColor === theme.name"
                        class="absolute inset-0 flex items-center justify-center rounded-full bg-black/20 dark:bg-white/20"
                    >
                        <Check class="h-5 w-5 text-white drop-shadow-md" />
                    </div>
                </div>

                <!-- Theme name -->
                <span class="text-xs font-medium transition-colors" :class="themeColor === theme.name ? 'text-foreground' : 'text-muted-foreground'">
                    {{ theme.label }}
                </span>
            </button>
        </div>

        <!-- Preview section -->
        <div class="mt-6 rounded-xl border bg-card p-4">
            <h4 class="mb-3 text-sm font-medium text-foreground">Preview</h4>
            <div class="flex flex-wrap gap-2">
                <button
                    class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                >
                    Primary Button
                </button>
                <button
                    class="rounded-lg bg-secondary px-4 py-2 text-sm font-medium text-secondary-foreground transition-colors hover:bg-secondary/80"
                >
                    Secondary
                </button>
                <button class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-accent-foreground transition-colors hover:bg-accent/80">
                    Accent
                </button>
                <button class="rounded-lg bg-muted px-4 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted/80">
                    Muted
                </button>
                <button
                    class="rounded-lg bg-destructive px-4 py-2 text-sm font-medium text-destructive-foreground transition-colors hover:bg-destructive/90"
                >
                    Destructive
                </button>
            </div>
            <div class="mt-3 flex items-center gap-2">
                <div class="h-3 w-3 rounded-full bg-primary"></div>
                <span class="text-xs text-muted-foreground">Primary</span>
                <div class="ml-2 h-3 w-3 rounded-full bg-accent"></div>
                <span class="text-xs text-muted-foreground">Accent</span>
                <div class="ml-2 h-3 w-3 rounded border border-border bg-card"></div>
                <span class="text-xs text-muted-foreground">Card</span>
            </div>
        </div>
    </div>
</template>
