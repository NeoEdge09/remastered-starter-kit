<script setup lang="ts">
import DynamicIcon from '@/components/DynamicIcon.vue';
import { DropdownMenuItem, DropdownMenuSub, DropdownMenuSubContent, DropdownMenuSubTrigger } from '@/components/ui/dropdown-menu';
import { useMenu } from '@/composables/useMenu';
import type { MenuItem } from '@/types';
import { Link } from '@inertiajs/vue3';

defineProps<{
    items: MenuItem[];
    depth?: number;
}>();

const { isActive, getMenuUrl } = useMenu();
</script>

<template>
    <template v-for="item in items" :key="item.id">
        <!-- Item with children - recursive sub-menu -->
        <DropdownMenuSub v-if="item.children && item.children.length > 0">
            <DropdownMenuSubTrigger class="flex items-center gap-2" :class="{ 'bg-accent': isActive(item) }">
                <DynamicIcon v-if="item.icon" :name="item.icon" class="h-4 w-4" />
                {{ item.name }}
            </DropdownMenuSubTrigger>
            <DropdownMenuSubContent class="w-48">
                <!-- Recursive call for unlimited nesting -->
                <DropdownMenuItemRecursive :items="item.children" :depth="(depth ?? 0) + 1" />
            </DropdownMenuSubContent>
        </DropdownMenuSub>

        <!-- Item without children - direct link -->
        <DropdownMenuItem v-else as-child :class="{ 'bg-accent': isActive(item) }">
            <Link :href="getMenuUrl(item)" class="flex w-full items-center gap-2">
                <DynamicIcon v-if="item.icon" :name="item.icon" class="h-4 w-4" />
                {{ item.name }}
            </Link>
        </DropdownMenuItem>
    </template>
</template>

<script lang="ts">
export default {
    name: 'DropdownMenuItemRecursive',
};
</script>
