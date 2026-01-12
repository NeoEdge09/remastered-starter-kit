<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import ToastNotification from '@/components/ToastNotification.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Combobox } from '@/components/ui/combobox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { computed } from 'vue';

interface Permission {
    id: number;
    name: string;
}

interface RouteAccess {
    id: number;
    route_name: string;
    route_uri: string | null;
    route_method: string | null;
    permission_name: string | null;
    permission_id: number | null;
    is_active: boolean;
    is_public: boolean;
    description: string | null;
    permission?: Permission | null;
}

const props = defineProps<{
    page_setting: {
        title: string;
        breadcrumbs: BreadcrumbItem[];
        back_link: string;
        action: string;
    };
    page_data: {
        routeAccess: RouteAccess;
        permissions: Permission[];
    };
}>();

const form = useForm({
    route_name: props.page_data.routeAccess.route_name,
    route_uri: props.page_data.routeAccess.route_uri || '',
    route_method: props.page_data.routeAccess.route_method || 'GET',
    permission_name: props.page_data.routeAccess.permission_name || '',
    is_active: props.page_data.routeAccess.is_active,
    is_public: props.page_data.routeAccess.is_public,
    description: props.page_data.routeAccess.description || '',
});

const methodOptions = [
    { value: 'GET', label: 'GET' },
    { value: 'POST', label: 'POST' },
    { value: 'PUT', label: 'PUT' },
    { value: 'PATCH', label: 'PATCH' },
    { value: 'DELETE', label: 'DELETE' },
];

const permissionOptions = computed(() => [
    { value: '', label: 'No permission (public or use is_public flag)' },
    ...props.page_data.permissions.map((p) => ({
        value: p.name,
        label: p.name,
    })),
]);

const submit = () => {
    form.put(props.page_setting.action);
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
                            <CardDescription>Update route access configuration</CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <form class="space-y-6" @submit.prevent="submit">
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

                        <!-- Current Permission Info -->
                        <div v-if="page_data.routeAccess.permission" class="rounded-lg bg-muted p-3">
                            <p class="text-sm">
                                <span class="font-medium">Currently Linked:</span>
                                <code class="ml-2 rounded bg-background px-1 py-0.5 text-xs">{{ page_data.routeAccess.permission.name }}</code>
                            </p>
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
                                Update Route Access
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
