<script setup lang="ts">
import DropdownMenuItemRecursive from '@/components/DropdownMenuItemRecursive.vue';
import DynamicIcon from '@/components/DynamicIcon.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useMenu } from '@/composables/useMenu';
import type { MenuItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ChevronDown } from 'lucide-vue-next';

const { menus, isActive, hasActiveChild, getMenuUrl } = useMenu();

// Check if menu or any children is active for styling
const isMenuActive = (menu: MenuItem): boolean => {
    return isActive(menu) || hasActiveChild(menu);
};
</script>

<template>
    <nav class="flex h-full items-stretch space-x-1">
        <template v-for="menu in menus" :key="menu?.id ?? Math.random()">
            <template v-if="menu">
                <!-- Menu with children - show dropdown -->
                <div v-if="menu.children && menu.children.length > 0" class="relative flex h-full items-center">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="ghost"
                                :class="[
                                    'h-9 gap-2 px-3 font-normal',
                                    isMenuActive(menu) ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100' : '',
                                ]"
                            >
                                <DynamicIcon v-if="menu.icon" :name="menu.icon" class="h-4 w-4" />
                                {{ menu.name }}
                                <ChevronDown class="h-3 w-3 opacity-50" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" class="w-48">
                            <!-- Use recursive component for unlimited nesting -->
                            <DropdownMenuItemRecursive :items="menu.children" :depth="1" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <!-- Active indicator line -->
                    <div v-if="isMenuActive(menu)" class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"></div>
                </div>

                <!-- Menu without children - direct link -->
                <div v-else class="relative flex h-full items-center">
                    <Button
                        as-child
                        variant="ghost"
                        :class="['h-9 gap-2 px-3 font-normal', isActive(menu) ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100' : '']"
                    >
                        <Link :href="getMenuUrl(menu)">
                            <DynamicIcon v-if="menu.icon" :name="menu.icon" class="h-4 w-4" />
                            {{ menu.name }}
                        </Link>
                    </Button>
                    <!-- Active indicator line -->
                    <div v-if="isActive(menu)" class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"></div>
                </div>
            </template>
        </template>
    </nav>
</template>
