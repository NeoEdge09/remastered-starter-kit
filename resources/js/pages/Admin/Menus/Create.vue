<script setup lang="ts">
import IconPicker from '@/components/IconPicker.vue';
import InputError from '@/components/InputError.vue';
import ToastNotification from '@/components/ToastNotification.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Combobox, type ComboboxOption } from '@/components/ui/combobox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { minLength, numeric, required, useValidation } from '@/composables/useValidation';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Menu, Permission } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface RouteOption {
    value: string;
    label: string;
}

const props = defineProps<{
    page_setting: {
        title: string;
        subtitle: string;
        breadcrumbs: BreadcrumbItem[];
        back_link: string;
        action: string;
    };
    page_data: {
        menus: Menu[];
        permissions: Permission[];
        routes: RouteOption[];
    };
}>();

const form = useForm({
    name: '',
    route_name: '',
    url: '',
    icon: '',
    parent_id: null as number | null,
    order: 0,
    permission_name: '',
    is_active: true,
});

// Create a reactive data object for validation
const formDataRef = ref({
    name: form.name,
    order: form.order,
});

// Sync form data with formDataRef
watch(
    () => ({ name: form.name, order: form.order }),
    (newVal) => {
        formDataRef.value = { ...newVal };
    },
    { deep: true },
);

// Validation rules
const validationRules = {
    name: [required('Menu name'), minLength('Menu name', 2)],
    order: [numeric('Order')],
};

const { errors: validationErrors, validateField, validate, clearErrors } = useValidation(formDataRef, validationRules);

const submit = () => {
    clearErrors();
    const isValid = validate();
    if (!isValid) return;

    form.post(props.page_setting.action);
};

// Flatten menus for parent selection
const flattenMenusForSelect = (menus: Menu[], depth: number = 0): ComboboxOption[] => {
    const result: ComboboxOption[] = [];
    for (const menu of menus) {
        const prefix = '\u00A0'.repeat(depth * 4);
        result.push({
            value: menu.id.toString(),
            label: `${prefix}${menu.name}`,
        });
        if (menu.children && menu.children.length > 0) {
            result.push(...flattenMenusForSelect(menu.children, depth + 1));
        }
    }
    return result;
};

const parentOptions = computed(() => [{ value: '', label: '-- No Parent (Top Level) --' }, ...flattenMenusForSelect(props.page_data.menus)]);

const permissionOptions = computed(() => [
    { value: '', label: '-- No Permission Required --' },
    ...props.page_data.permissions.map((p) => ({
        value: p.name,
        label: p.name,
    })),
]);

const routeOptions = computed(() => [{ value: '', label: '-- No Route (Use URL) --' }, ...props.page_data.routes]);
</script>

<template>
    <Head :title="page_setting.title" />
    <ToastNotification />

    <AppLayout :breadcrumbs="page_setting.breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center gap-4">
                <Button variant="ghost" size="icon" as-child>
                    <Link :href="page_setting.back_link">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <h1 class="text-2xl font-bold">{{ page_setting.title }}</h1>
            </div>

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Menu Information</CardTitle>
                        <CardDescription>{{ page_setting.subtitle }}</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Menu Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., Dashboard, Settings"
                                required
                                @blur="validateField('name')"
                            />
                            <InputError :message="validationErrors.name || form.errors.name" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="route_name">Route Name</Label>
                                <Combobox v-model="form.route_name" :options="routeOptions" search-placeholder="Search routes..." />
                                <p class="text-sm text-muted-foreground">Select a Laravel route (takes priority over URL)</p>
                                <InputError :message="form.errors.route_name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="url">URL</Label>
                                <Input id="url" v-model="form.url" type="text" placeholder="e.g., /admin/users or https://..." />
                                <p class="text-sm text-muted-foreground">Used if route name is not set</p>
                                <InputError :message="form.errors.url" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label>Icon</Label>
                            <IconPicker v-model="form.icon" />
                            <p class="text-sm text-muted-foreground">Click to browse and select a Lucide icon</p>
                            <InputError :message="form.errors.icon" />
                        </div>

                        <div class="space-y-2">
                            <Label for="parent_id">Parent Menu</Label>
                            <Combobox v-model="form.parent_id" :options="parentOptions" search-placeholder="Search menus..." />
                            <InputError :message="form.errors.parent_id" />
                        </div>

                        <div class="space-y-2">
                            <Label for="order">Display Order</Label>
                            <Input id="order" v-model.number="form.order" type="number" min="0" @blur="validateField('order')" />
                            <InputError :message="validationErrors.order || form.errors.order" />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Access Control</CardTitle>
                        <CardDescription>Configure menu visibility</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="permission_name">Required Permission</Label>
                            <Combobox v-model="form.permission_name" :options="permissionOptions" search-placeholder="Search permissions..." />
                            <p class="text-sm text-muted-foreground">Users must have this permission to see this menu</p>
                            <InputError :message="form.errors.permission_name" />
                        </div>

                        <div class="flex items-center justify-between rounded-lg border p-4">
                            <div class="space-y-0.5">
                                <Label>Active</Label>
                                <p class="text-sm text-muted-foreground">Enable or disable this menu item</p>
                            </div>
                            <Switch v-model:checked="form.is_active" />
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="page_setting.back_link">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing"> Create Menu </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
