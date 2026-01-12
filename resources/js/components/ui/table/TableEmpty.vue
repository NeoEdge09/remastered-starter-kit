<script setup lang="ts">
import { cn } from '@/lib/utils';
import type { HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    colspan?: number;
}>();
</script>

<template>
    <tr>
        <td :colspan="colspan" :class="cn('h-24 text-center', props.class)">
            <slot />
        </td>
    </tr>
</template>
