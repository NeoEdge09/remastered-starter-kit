<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import LayoutTabs from '@/components/LayoutTabs.vue';
import ThemeColorPicker from '@/components/ThemeColorPicker.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall title="Appearance settings" description="Update your account's appearance settings" />

                <div class="space-y-6">
                    <div>
                        <h3 class="mb-2 text-sm font-medium">Theme</h3>
                        <p class="mb-3 text-sm text-muted-foreground">Choose between light, dark, or system theme</p>
                        <AppearanceTabs />
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="mb-2 text-sm font-medium">Color Palette</h3>
                        <p class="mb-4 text-sm text-muted-foreground">Choose a color palette that suits your style</p>
                        <ThemeColorPicker />
                    </div>

                    <div class="border-t pt-6">
                        <h3 class="mb-2 text-sm font-medium">Layout</h3>
                        <p class="mb-3 text-sm text-muted-foreground">Choose your preferred navigation layout</p>
                        <LayoutTabs />
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
