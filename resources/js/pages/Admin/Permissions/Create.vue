<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import ToastNotification from '@/components/ToastNotification.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Combobox, type ComboboxOption } from '@/components/ui/combobox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { minLength, required, useValidation } from '@/composables/useValidation';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, PermissionGroup } from '@/types';
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
        permissionGroups: PermissionGroup[];
    };
}>();

const form = useForm({
    name: '',
    description: '',
    permission_group_id: '',
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
    name: [required('Permission name'), minLength('Permission name', 2)],
};

const { errors: validationErrors, validateField, validate, clearErrors } = useValidation(formDataRef, validationRules);

const groupOptions: ComboboxOption[] = [
    { value: '', label: 'No Group' },
    ...props.page_data.permissionGroups.map((g) => ({ value: g.id.toString(), label: g.name })),
];

const submit = () => {
    clearErrors();
    const isValid = validate();
    if (!isValid) return;

    form.post(props.page_setting.action);
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

            <form @submit.prevent="submit" class="max-w-2xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Permission Information</CardTitle>
                        <CardDescription>Define a new permission</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Permission Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., user.create, post.edit"
                                required
                                @blur="validateField('name')"
                            />
                            <p class="text-sm text-muted-foreground">Use dot notation for clarity: resource.action (e.g., user.create)</p>
                            <InputError :message="validationErrors.name || form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Describe what this permission allows" :rows="3" />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="space-y-2">
                            <Label for="permission_group_id">Permission Group</Label>
                            <Combobox
                                v-model="form.permission_group_id"
                                :options="groupOptions"
                                placeholder="Select a group"
                                search-placeholder="Search groups..."
                            />
                            <InputError :message="form.errors.permission_group_id" />
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="page_setting.back_link">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing"> Create Permission </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
