<script setup lang="ts">
import DynamicIcon from '@/components/DynamicIcon.vue';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { SidebarMenuSub, SidebarMenuSubButton, SidebarMenuSubItem } from '@/components/ui/sidebar';
import { useMenu } from '@/composables/useMenu';
import type { MenuItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';

const props = defineProps<{
    items: MenuItem[];
    depth?: number;
    openItems: Record<number, boolean>;
}>();

const emit = defineEmits<{
    toggle: [id: number];
    setOpen: [id: number, value: boolean];
}>();

const { isActive, hasActiveChild, getMenuUrl } = useMenu();

const isOpen = (menu: MenuItem): boolean => {
    return props.openItems[menu.id] ?? false;
};
</script>

<template>
    <template v-for="item in items" :key="item?.id ?? Math.random()">
        <template v-if="item">
            <!-- Item with children (nested) -->
            <SidebarMenuSubItem v-if="item.children && item.children.length > 0">
                <Collapsible class="w-full" :open="isOpen(item)" @update:open="(val: boolean) => emit('setOpen', item.id, val)">
                    <CollapsibleTrigger as-child>
                        <SidebarMenuSubButton class="w-full cursor-pointer" :is-active="isActive(item) || hasActiveChild(item)">
                            <DynamicIcon v-if="item.icon" :name="item.icon" />
                            <span class="flex-1">{{ item.name }}</span>
                            <ChevronRight class="ml-auto h-4 w-4 transition-transform duration-200" :class="{ 'rotate-90': isOpen(item) }" />
                        </SidebarMenuSubButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenuSub>
                            <!-- Recursive call for deeper nesting -->
                            <NavMenuItem
                                :items="item.children"
                                :depth="(depth ?? 0) + 1"
                                :open-items="openItems"
                                @toggle="emit('toggle', $event)"
                                @set-open="(id: number, val: boolean) => emit('setOpen', id, val)"
                            />
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </Collapsible>
            </SidebarMenuSubItem>

            <!-- Item without children (leaf) -->
            <SidebarMenuSubItem v-else>
                <SidebarMenuSubButton as-child :is-active="isActive(item)">
                    <Link :href="getMenuUrl(item)">
                        <DynamicIcon v-if="item.icon" :name="item.icon" />
                        <span>{{ item.name }}</span>
                    </Link>
                </SidebarMenuSubButton>
            </SidebarMenuSubItem>
        </template>
    </template>
</template>
