<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import ToastNotification from '@/components/ToastNotification.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { confirmed, email, minLength, required, useValidation } from '@/composables/useValidation';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Permission, Role } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    page_setting: {
        title: string;
        breadcrumbs: BreadcrumbItem[];
        back_link: string;
        action: string;
    };
    page_data: {
        roles: Role[];
        permissions: Record<string, Permission[]>;
    };
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    is_active: true as boolean,
    roles: [] as number[],
    permissions: [] as number[],
});

// Create a ref wrapper for validation
const formDataRef = ref(form.data());

// Sync form data to ref for validation
const syncFormData = () => {
    formDataRef.value = form.data();
};

// Client-side validation
const {
    errors: validationErrors,
    validate,
    onFieldBlur: _onFieldBlur,
    hasErrors,
} = useValidation(formDataRef, {
    name: [required('Name'), minLength('Name', 2)],
    email: [required('Email'), email('Email')],
    password: [required('Password'), minLength('Password', 8)],
    password_confirmation: [required('Password Confirmation'), confirmed('Password', 'password')],
});

const onFieldBlur = (field: string) => {
    syncFormData();
    _onFieldBlur(field);
};

const submit = () => {
    syncFormData();
    // Validate before submit
    if (!validate()) {
        return;
    }
    form.post(props.page_setting.action);
};

const toggleRole = (roleId: number) => {
    const index = form.roles.indexOf(roleId);
    if (index === -1) {
        form.roles.push(roleId);
    } else {
        form.roles.splice(index, 1);
    }
};

const togglePermission = (permissionId: number) => {
    const index = form.permissions.indexOf(permissionId);
    if (index === -1) {
        form.permissions.push(permissionId);
    } else {
        form.permissions.splice(index, 1);
    }
};

const isRoleSelected = (roleId: number) => form.roles.includes(roleId);
const isPermissionSelected = (permissionId: number) => form.permissions.includes(permissionId);

const permissionGroups = computed(() => {
    return Object.entries(props.page_data.permissions).map(([groupId, permissions]) => ({
        id: groupId,
        name: permissions[0]?.group?.name || 'Uncategorized',
        permissions,
    }));
});

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
                <!-- User Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>User Information</CardTitle>
                        <CardDescription>Basic user account details</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Name</Label>
                                <Input id="name" v-model="form.name" type="text" placeholder="Enter name" @blur="onFieldBlur('name')" />
                                <InputError :message="validationErrors.name || form.errors.name" />
                            </div>
                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input id="email" v-model="form.email" type="email" placeholder="Enter email" @blur="onFieldBlur('email')" />
                                <InputError :message="validationErrors.email || form.errors.email" />
                            </div>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="password">Password</Label>
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    placeholder="Enter password"
                                    @blur="onFieldBlur('password')"
                                />
                                <InputError :message="validationErrors.password || form.errors.password" />
                            </div>
                            <div class="space-y-2">
                                <Label for="password_confirmation">Confirm Password</Label>
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    placeholder="Confirm password"
                                    @blur="onFieldBlur('password_confirmation')"
                                />
                                <InputError :message="validationErrors.password_confirmation" />
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <Switch id="is_active" :checked="form.is_active" @update:checked="form.is_active = $event" />
                            <Label for="is_active">Active</Label>
                        </div>
                    </CardContent>
                </Card>

                <!-- Roles -->
                <Card>
                    <CardHeader>
                        <CardTitle>Roles</CardTitle>
                        <CardDescription>Assign roles to this user</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-4">
                            <div v-for="role in page_data.roles" :key="role.id" class="flex items-center gap-2">
                                <Checkbox :id="`role-${role.id}`" :checked="isRoleSelected(role.id)" @update:checked="toggleRole(role.id)" />
                                <Label :for="`role-${role.id}`" class="cursor-pointer">
                                    {{ role.name }}
                                </Label>
                            </div>
                        </div>
                        <InputError :message="form.errors.roles" />
                    </CardContent>
                </Card>

                <!-- Direct Permissions -->
                <Card>
                    <CardHeader>
                        <CardTitle>Direct Permissions</CardTitle>
                        <CardDescription>Assign permissions directly to this user (in addition to role permissions)</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div v-for="group in permissionGroups" :key="group.id" class="space-y-3">
                            <div class="flex items-center gap-2 border-b pb-2">
                                <Checkbox
                                    :id="`group-${group.id}`"
                                    :checked="isGroupAllSelected(group.permissions)"
                                    @update:checked="toggleGroupPermissions(group.permissions)"
                                />
                                <Label :for="`group-${group.id}`" class="cursor-pointer font-semibold">
                                    {{ group.name }}
                                </Label>
                            </div>
                            <div class="ml-6 grid gap-2 md:grid-cols-2 lg:grid-cols-3">
                                <div v-for="permission in group.permissions" :key="permission.id" class="flex items-center gap-2">
                                    <Checkbox
                                        :id="`permission-${permission.id}`"
                                        :checked="isPermissionSelected(permission.id)"
                                        @update:checked="togglePermission(permission.id)"
                                    />
                                    <Label :for="`permission-${permission.id}`" class="cursor-pointer text-sm">
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
                        <Link :href="route('admin.users.index')">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing"> Create User </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
