<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import DynamicIcon from '@/components/DynamicIcon.vue';
import NavDynamicHeader from '@/components/NavDynamicHeader.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    Breadcrumb,
    BreadcrumbItem as BreadcrumbItemUI,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { useMenu } from '@/composables/useMenu';
import type { BreadcrumbItem, MenuItem, NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { ChevronRight, Menu } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const { menus, isActive, hasActiveChild, getMenuUrl } = useMenu();

const activeItemStyles = computed(() => (url: string) => (page.url === url ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100' : ''));

const rightNavItems: NavItem[] = [];

// Mobile menu expandable state
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

const isOpen = (menu: MenuItem): boolean => {
    return openItems.value[menu.id] ?? false;
};
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="mr-2 h-9 w-9">
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only">Navigation Menu</SheetTitle>
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon class="size-6 fill-current text-black dark:text-white" />
                            </SheetHeader>
                            <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                <nav class="-mx-3 space-y-1">
                                    <!-- Dynamic Menus -->
                                    <template v-for="menu in menus" :key="menu?.id">
                                        <template v-if="menu">
                                            <!-- Menu with children (expandable) -->
                                            <Collapsible
                                                v-if="menu.children && menu.children.length > 0"
                                                :open="isOpen(menu)"
                                                @update:open="(val: boolean) => setOpen(menu.id, val)"
                                            >
                                                <CollapsibleTrigger as-child>
                                                    <button
                                                        class="flex w-full items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                                        :class="{ 'bg-accent': isActive(menu) || hasActiveChild(menu) }"
                                                    >
                                                        <DynamicIcon v-if="menu.icon" :name="menu.icon" class="h-5 w-5" />
                                                        <span class="flex-1 text-left">{{ menu.name }}</span>
                                                        <ChevronRight
                                                            class="h-4 w-4 transition-transform duration-200"
                                                            :class="{ 'rotate-90': isOpen(menu) }"
                                                        />
                                                    </button>
                                                </CollapsibleTrigger>
                                                <CollapsibleContent>
                                                    <div class="ml-4 space-y-1 border-l pl-2">
                                                        <Link
                                                            v-for="child in menu.children"
                                                            :key="child.id"
                                                            :href="getMenuUrl(child)"
                                                            class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                                            :class="{ 'bg-accent': isActive(child) }"
                                                        >
                                                            <DynamicIcon v-if="child.icon" :name="child.icon" class="h-4 w-4" />
                                                            {{ child.name }}
                                                        </Link>
                                                    </div>
                                                </CollapsibleContent>
                                            </Collapsible>

                                            <!-- Menu without children -->
                                            <Link
                                                v-else
                                                :href="getMenuUrl(menu)"
                                                class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                                :class="{ 'bg-accent': isActive(menu) }"
                                            >
                                                <DynamicIcon v-if="menu.icon" :name="menu.icon" class="h-5 w-5" />
                                                {{ menu.name }}
                                            </Link>
                                        </template>
                                    </template>
                                </nav>
                                <div class="flex flex-col space-y-4">
                                    <a
                                        v-for="item in rightNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="flex items-center space-x-2 text-sm font-medium"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                        <span>{{ item.title }}</span>
                                    </a>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="route('dashboard')" class="flex items-center gap-x-2">
                    <AppLogo class="hidden h-6 xl:block" />
                </Link>

                <!-- Desktop Menu - Dynamic Navigation -->
                <div class="ml-10 hidden h-full lg:flex lg:flex-1">
                    <NavDynamicHeader />
                </div>

                <div class="ml-auto flex items-center space-x-2">
                    <div class="relative flex items-center space-x-1">
                        <div class="hidden space-x-1 lg:flex">
                            <template v-for="item in rightNavItems" :key="item.title">
                                <TooltipProvider :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Button variant="ghost" size="icon" as-child class="group h-9 w-9 cursor-pointer">
                                                <a :href="item.href" target="_blank" rel="noopener noreferrer">
                                                    <span class="sr-only">{{ item.title }}</span>
                                                    <component :is="item.icon" class="size-5 opacity-80 group-hover:opacity-100" />
                                                </a>
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{{ item.title }}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </template>
                        </div>
                    </div>

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage :src="auth.user.avatar" :alt="auth.user.name" />
                                    <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div v-if="props.breadcrumbs.length > 1" class="main-gradient-bg flex w-full">
            <div class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl">
                <Breadcrumb>
                    <BreadcrumbList>
                        <template v-for="(item, index) in breadcrumbs" :key="index">
                            <BreadcrumbItemUI>
                                <template v-if="index === breadcrumbs.length - 1">
                                    <BreadcrumbPage>{{ item.title }}</BreadcrumbPage>
                                </template>
                                <template v-else>
                                    <BreadcrumbLink :href="item.href">
                                        {{ item.title }}
                                    </BreadcrumbLink>
                                </template>
                            </BreadcrumbItemUI>
                            <BreadcrumbSeparator v-if="index !== breadcrumbs.length - 1" />
                        </template>
                    </BreadcrumbList>
                </Breadcrumb>
            </div>
        </div>
    </div>
</template>
