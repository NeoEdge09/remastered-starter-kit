<script setup lang="ts">
import { cn } from '@/lib/utils';
import { type VariantProps, cva } from 'class-variance-authority';
import { computed, type HTMLAttributes } from 'vue';

const badgeVariants = cva(
    'inline-flex items-center rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
    {
        variants: {
            variant: {
                default: 'border-transparent bg-primary text-primary-foreground shadow hover:bg-primary/80',
                secondary: 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
                destructive: 'border-transparent bg-destructive text-destructive-foreground shadow hover:bg-destructive/80',
                outline: 'text-foreground',
                success: 'border-transparent bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100',
                warning: 'border-transparent bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100',
            },
        },
        defaultVariants: {
            variant: 'default',
        },
    },
);

type BadgeVariants = VariantProps<typeof badgeVariants>;

const props = defineProps<{
    class?: HTMLAttributes['class'];
    variant?: BadgeVariants['variant'];
}>();
</script>

<template>
    <div :class="cn(badgeVariants({ variant }), props.class)">
        <slot />
    </div>
</template>
