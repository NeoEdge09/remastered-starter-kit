<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import ToastNotification from '@/components/ToastNotification.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Combobox, type ComboboxOption } from '@/components/ui/combobox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { computed, watch } from 'vue';

interface Permission {
    id: number;
    name: string;
}

interface AvailableRoute {
    name: string;
    uri: string;
    method: string;
}

const props = defineProps<{
    page_setting: {
        title: string;
        breadcrumbs: BreadcrumbItem[];
        back_link: string;
        action: string;
    };
    page_data: {
        permissions: Permission[];
        availableRoutes: AvailableRoute[];
    };
}>();

const form = useForm({
    route_name: '',
    route_uri: '',
    route_method: 'GET',
    permission_name: '',
    is_active: true,
    is_public: false,
    description: '',
});

const methodOptions = [
    { value: 'GET', label: 'GET' },
    { value: 'POST', label: 'POST' },
    { value: 'PUT', label: 'PUT' },
    { value: 'PATCH', label: 'PATCH' },
    { value: 'DELETE', label: 'DELETE' },
];

const routeOptions = computed<ComboboxOption[]>(() => [
    { value: '', label: 'Select a route or enter manually' },
    ...props.page_data.availableRoutes.map((r) => ({
        value: r.name,
        label: `${r.name} (${r.method} ${r.uri})`,
    })),
]);

// Auto-fill when selecting a route
watch(
    () => form.route_name,
    (newValue) => {
        const selectedRoute = props.page_data.availableRoutes.find((r) => r.name === newValue);
        if (selectedRoute) {
            form.route_uri = selectedRoute.uri;
            form.route_method = selectedRoute.method;
            // Auto-suggest permission name (match from existing)
            const suggested = generatePermissionName(selectedRoute.name);
            const existingPermission = props.page_data.permissions.find((p) => p.name === suggested);
            form.permission_name = existingPermission ? existingPermission.name : '';
        }
    },
);

const generatePermissionName = (routeName: string): string => {
    const parts = routeName.split('.');

    // Remove common prefixes
    const prefixes = ['admin', 'api', 'web'];
    if (prefixes.includes(parts[0])) {
        parts.shift();
    }

    if (parts.length < 2) {
        return parts.join('.');
    }

    // Map common actions
    const actionMap: Record<string, string> = {
        index: 'view',
        show: 'view',
        create: 'create',
        store: 'create',
        edit: 'update',
        update: 'update',
        destroy: 'delete',
        bulkDestroy: 'delete',
        export: 'export',
        import: 'import',
    };

    const resource = parts[0].replace(/-/g, '-');
    const action = parts[parts.length - 1];
    const singular = resource.endsWith('s') ? resource.slice(0, -1) : resource;
    const mappedAction = actionMap[action] || action.replace(/([A-Z])/g, '-$1').toLowerCase();

    return `${singular}.${mappedAction}`;
};

const permissionOptions = computed(() => [
    { value: '', label: 'No permission (public or use is_public flag)' },
    ...props.page_data.permissions.map((p) => ({
        value: p.name,
        label: p.name,
    })),
]);

const submit = () => {
    form.post(props.page_setting.action);
};
</script>

<template>
    <Head :title="page_setting.title" />
    <ToastNotification />

    <AppLayout :breadcrumbs="page_setting.breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="mx-auto w-full max-w-2xl">
                <CardHeader>
                    <div class="flex items-center gap-4">
                        <Button variant="ghost" size="icon" as-child>
                            <Link :href="page_setting.back_link">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <CardTitle>{{ page_setting.title }}</CardTitle>
                            <CardDescription>Add a new route access configuration</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <form class="space-y-6" @submit.prevent="submit">
                        <!-- Route Selection -->
                        <div class="space-y-2">
                            <Label for="route_select">Select Route</Label>
                            <Combobox v-model="form.route_name" :options="routeOptions" search-placeholder="Search routes..." />
                            <p class="text-xs text-muted-foreground">Select from unregistered routes or enter manually below</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <!-- Route Name -->
                            <div class="space-y-2">
                                <Label for="route_name">Route Name <span class="text-destructive">*</span></Label>
                                <Input
                                    id="route_name"
                                    v-model="form.route_name"
                                    placeholder="admin.users.index"
                                    :class="{ 'border-destructive': form.errors.route_name }"
                                />
                                <InputError :message="form.errors.route_name" />
                            </div>

                            <!-- Route URI -->
                            <div class="space-y-2">
                                <Label for="route_uri">Route URI</Label>
                                <Input
                                    id="route_uri"
                                    v-model="form.route_uri"
                                    placeholder="/admin/users"
                                    :class="{ 'border-destructive': form.errors.route_uri }"
                                />
                                <InputError :message="form.errors.route_uri" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <!-- Route Method -->
                            <div class="space-y-2">
                                <Label for="route_method">HTTP Method</Label>
                                <Select id="route_method" v-model="form.route_method" :options="methodOptions" />
                            </div>

                            <!-- Permission Name -->
                            <div class="space-y-2">
                                <Label for="permission_name">Permission</Label>
                                <Combobox
                                    v-model="form.permission_name"
                                    :options="permissionOptions"
                                    placeholder="Select a permission"
                                    search-placeholder="Search permissions..."
                                />
                                <InputError :message="form.errors.permission_name" />
                                <p class="text-xs text-muted-foreground">Select from existing permissions or leave empty for public access</p>
                            </div>
                        </div>

                        <!-- Switches -->
                        <div class="flex flex-wrap gap-6">
                            <div class="flex items-center gap-2">
                                <Switch id="is_active" v-model:checked="form.is_active" />
                                <Label for="is_active">Active</Label>
                            </div>
                            <div class="flex items-center gap-2">
                                <Switch id="is_public" v-model:checked="form.is_public" />
                                <Label for="is_public">Public (no permission check)</Label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Optional description for this route access..."
                                :rows="3"
                            />
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-4">
                            <Button variant="outline" type="button" as-child>
                                <Link :href="page_setting.back_link">Cancel</Link>
                            </Button>
                            <Button type="submit" :disabled="form.processing">
                                <Save class="mr-2 h-4 w-4" />
                                Create Route Access
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
