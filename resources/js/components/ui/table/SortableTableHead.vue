<script setup lang="ts">
import { cn } from '@/lib/utils';
import { ArrowDown, ArrowUp, ArrowUpDown } from 'lucide-vue-next';
import { computed } from 'vue';

type SortDirection = 'asc' | 'desc' | null;

const props = withDefaults(
    defineProps<{
        column: string;
        sortColumn?: string | null;
        sortDirection?: SortDirection;
        class?: string;
    }>(),
    {
        sortColumn: null,
        sortDirection: null,
    },
);

const emit = defineEmits<{
    sort: [column: string];
}>();

const isSorted = computed(() => props.sortColumn === props.column);
const direction = computed(() => (isSorted.value ? props.sortDirection : null));

const handleClick = () => {
    emit('sort', props.column);
};
</script>

<template>
    <th
        :class="
            cn(
                'h-10 px-2 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-[2px]',
                'cursor-pointer select-none hover:bg-muted/50 transition-colors',
                props.class,
            )
        "
        @click="handleClick"
    >
        <div class="flex items-center gap-1">
            <slot />
            <span class="ml-1">
                <ArrowUp v-if="direction === 'asc'" class="h-4 w-4" />
                <ArrowDown v-else-if="direction === 'desc'" class="h-4 w-4" />
                <ArrowUpDown v-else class="h-4 w-4 text-muted-foreground/50" />
            </span>
        </div>
    </th>
</template>
