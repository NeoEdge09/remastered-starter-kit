<script setup lang="ts">
import DynamicIcon from '@/components/DynamicIcon.vue';
import NavMenuItem from '@/components/NavMenuItem.vue';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem, SidebarMenuSub } from '@/components/ui/sidebar';
import { useMenu } from '@/composables/useMenu';
import type { MenuItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps<{
    label?: string;
}>();

const { menus, isActive, hasActiveChild, getMenuUrl } = useMenu();

// Track which collapsibles are open (shared across all levels)
const openItems = ref<Record<number, boolean>>({});

// Initialize open state for menus with active children
const initializeOpenState = () => {
    menus.value.forEach((menu) => {
        if (menu && hasActiveChild(menu)) {
            openItems.value[menu.id] = true;
        }
    });
};
initializeOpenState();

const setOpen = (id: number, value: boolean) => {
    openItems.value[id] = value;
};

const toggleItem = (id: number) => {
    openItems.value[id] = !openItems.value[id];
};

const isOpen = (menu: MenuItem): boolean => {
    return openItems.value[menu.id] ?? false;
};
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel v-if="label">{{ label }}</SidebarGroupLabel>
        <SidebarMenu>
            <template v-for="menu in menus" :key="menu?.id ?? Math.random()">
                <template v-if="menu">
                    <!-- Menu with children -->
                    <Collapsible
                        v-if="menu.children && menu.children.length > 0"
                        as-child
                        :open="isOpen(menu)"
                        @update:open="(val: boolean) => setOpen(menu.id, val)"
                    >
                        <SidebarMenuItem>
                            <CollapsibleTrigger as-child>
                                <SidebarMenuButton :is-active="isActive(menu) || hasActiveChild(menu)">
                                    <DynamicIcon v-if="menu.icon" :name="menu.icon" />
                                    <span>{{ menu.name }}</span>
                                    <ChevronRight class="ml-auto transition-transform duration-200" :class="{ 'rotate-90': isOpen(menu) }" />
                                </SidebarMenuButton>
                            </CollapsibleTrigger>
                            <CollapsibleContent>
                                <SidebarMenuSub>
                                    <!-- Use recursive NavMenuItem for unlimited nesting -->
                                    <NavMenuItem :items="menu.children" :depth="1" :open-items="openItems" @toggle="toggleItem" @set-open="setOpen" />
                                </SidebarMenuSub>
                            </CollapsibleContent>
                        </SidebarMenuItem>
                    </Collapsible>

                    <!-- Menu without children -->
                    <SidebarMenuItem v-else>
                        <SidebarMenuButton as-child :is-active="isActive(menu)">
                            <Link :href="getMenuUrl(menu)">
                                <DynamicIcon v-if="menu.icon" :name="menu.icon" />
                                <span>{{ menu.name }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </template>
            </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
