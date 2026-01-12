<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import ToastNotification from '@/components/ToastNotification.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { minLength, required, useValidation } from '@/composables/useValidation';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Permission, PermissionGroup } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    page_setting: {
        title: string;
        breadcrumbs: BreadcrumbItem[];
        back_link: string;
        action: string;
    };
    page_data: {
        permissionGroups: (PermissionGroup & { permissions: Permission[] })[];
    };
}>();

const form = useForm({
    name: '',
    permissions: [] as number[],
});

// Create a reactive data object for validation
const formDataRef = ref({
    name: form.name,
});

// Sync form data with formDataRef
watch(
    () => ({ name: form.name }),
    (newVal) => {
        formDataRef.value = { ...newVal };
    },
    { deep: true },
);

// Validation rules
const validationRules = {
    name: [required('Role name'), minLength('Role name', 2)],
};

const { errors: validationErrors, validateField, validate, clearErrors } = useValidation(formDataRef, validationRules);

const submit = () => {
    clearErrors();
    const isValid = validate();
    if (!isValid) return;

    form.post(props.page_setting.action);
};

const togglePermission = (permissionId: number) => {
    const index = form.permissions.indexOf(permissionId);
    if (index === -1) {
        form.permissions.push(permissionId);
    } else {
        form.permissions.splice(index, 1);
    }
};

const isPermissionSelected = (permissionId: number) => form.permissions.includes(permissionId);

const toggleGroupPermissions = (permissions: Permission[]) => {
    const allSelected = permissions.every((p) => form.permissions.includes(p.id));
    if (allSelected) {
        form.permissions = form.permissions.filter((id) => !permissions.find((p) => p.id === id));
    } else {
        const newIds = permissions.map((p) => p.id).filter((id) => !form.permissions.includes(id));
        form.permissions.push(...newIds);
    }
};

const isGroupAllSelected = (permissions: Permission[]) => {
    return permissions.length > 0 && permissions.every((p) => form.permissions.includes(p.id));
};

const selectAll = () => {
    form.permissions = props.page_data.permissionGroups.flatMap((g) => (g.permissions ? g.permissions.map((p) => p.id) : []));
};

const deselectAll = () => {
    form.permissions = [];
};
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

            <form @submit.prevent="submit" class="space-y-6">
                <!-- Role Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>Role Information</CardTitle>
                        <CardDescription>Basic role details</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="max-w-md space-y-2">
                            <Label for="name">Role Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., editor, manager, viewer"
                                required
                                @blur="validateField('name')"
                            />
                            <InputError :message="validationErrors.name || form.errors.name" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Permissions -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Permissions</CardTitle>
                                <CardDescription>Select permissions for this role</CardDescription>
                            </div>
                            <div class="flex gap-2">
                                <Button type="button" variant="outline" size="sm" @click="selectAll"> Select All </Button>
                                <Button type="button" variant="outline" size="sm" @click="deselectAll"> Deselect All </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div v-for="group in page_data.permissionGroups" :key="group.id" class="space-y-3">
                            <div class="flex items-center gap-2 border-b pb-2">
                                <Checkbox
                                    :id="`group-${group.id}`"
                                    :checked="isGroupAllSelected(group.permissions)"
                                    @update:checked="toggleGroupPermissions(group.permissions)"
                                />
                                <Label :for="`group-${group.id}`" class="cursor-pointer font-semibold">
                                    {{ group.name }}
                                </Label>
                                <span class="text-sm text-muted-foreground"> ({{ group.permissions.length }} permissions) </span>
                            </div>
                            <div class="ml-6 grid gap-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                                <div v-for="permission in group.permissions" :key="permission.id" class="flex items-center gap-2">
                                    <Checkbox
                                        :id="`permission-${permission.id}`"
                                        :checked="isPermissionSelected(permission.id)"
                                        @update:checked="togglePermission(permission.id)"
                                    />
                                    <Label :for="`permission-${permission.id}`" class="cursor-pointer text-sm" :title="permission.description || ''">
                                        {{ permission.name }}
                                    </Label>
                                </div>
                            </div>
                        </div>
                        <InputError :message="form.errors.permissions" />
                    </CardContent>
                </Card>

                <!-- Submit -->
                <div class="flex justify-end gap-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="page_setting.back_link">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing"> Create Role </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
