<script setup lang="ts">
import { icons } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    name: string;
    size?: number;
    strokeWidth?: number;
    class?: string;
}>();

const icon = computed(() => {
    // Convert kebab-case or snake_case to PascalCase
    const pascalCase = props.name
        .split(/[-_]/)
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
        .join('');

    return icons[pascalCase as keyof typeof icons] || icons.Circle;
});
</script>

<template>
    <component :is="icon" :size="size || 16" :stroke-width="strokeWidth || 2" :class="props.class" />
</template>
