<script setup lang="ts">
import AppHeaderLayout from '@/layouts/app/AppHeaderLayout.vue';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { computed, onMounted, ref } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

type LayoutType = 'sidebar' | 'header';
const layout = ref<LayoutType>('sidebar');

onMounted(() => {
    const saved = localStorage.getItem('layout-preference') as LayoutType | null;
    if (saved && (saved === 'sidebar' || saved === 'header')) {
        layout.value = saved;
    }
});

const LayoutComponent = computed(() => {
    return layout.value === 'header' ? AppHeaderLayout : AppSidebarLayout;
});
</script>

<template>
    <component :is="LayoutComponent" :breadcrumbs="breadcrumbs">
        <slot />
    </component>
</template>
