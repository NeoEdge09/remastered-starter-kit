<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import ToastNotification from '@/components/ToastNotification.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { minLength, numeric, required, useValidation } from '@/composables/useValidation';
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
        permissionGroup: PermissionGroup;
    };
}>();

const form = useForm({
    name: props.page_data.permissionGroup.name,
    description: props.page_data.permissionGroup.description || '',
    order: props.page_data.permissionGroup.order,
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
    name: [required('Group name'), minLength('Group name', 2)],
    order: [numeric('Order')],
};

const { errors: validationErrors, validateField, validate, clearErrors } = useValidation(formDataRef, validationRules);

const submit = () => {
    clearErrors();
    const isValid = validate();
    if (!isValid) return;

    form.put(props.page_setting.action);
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
                        <CardTitle>Group Information</CardTitle>
                        <CardDescription>Update permission group details</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Group Name</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="e.g., User Management, Content Management"
                                required
                                @blur="validateField('name')"
                            />
                            <InputError :message="validationErrors.name || form.errors.name" />
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" placeholder="Describe the purpose of this group" :rows="3" />
                            <InputError :message="form.errors.description" />
                        </div>

                        <div class="space-y-2">
                            <Label for="order">Display Order</Label>
                            <Input id="order" v-model.number="form.order" type="number" min="0" @blur="validateField('order')" />
                            <InputError :message="validationErrors.order || form.errors.order" />
                        </div>
                    </CardContent>
                </Card>

                <!-- Permissions in this group -->
                <Card v-if="page_data.permissionGroup.permissions && page_data.permissionGroup.permissions.length > 0">
                    <CardHeader>
                        <CardTitle>Permissions in this Group</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-wrap gap-2">
                            <Badge v-for="permission in page_data.permissionGroup.permissions" :key="permission.id" variant="outline">
                                {{ permission.name }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-4">
                    <Button variant="outline" type="button" as-child>
                        <Link :href="page_setting.back_link">Cancel</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing"> Update Group </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
