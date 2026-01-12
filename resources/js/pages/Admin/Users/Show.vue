<script setup lang="ts">
import ToastNotification from '@/components/ToastNotification.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, User } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Edit } from 'lucide-vue-next';

const props = defineProps<{
    page_setting: {
        title: string;
        breadcrumbs: BreadcrumbItem[];
        back_link: string;
        action: string;
    };
    page_data: {
        user: {
            data: User;
        };
    };
}>();

</script>

<template>
    <Head :title="`${page_setting.title}`" />
    <ToastNotification />

    <AppLayout :breadcrumbs="page_setting.breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="page_setting.back_link">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <h1 class="text-2xl font-bold">{{ page_setting.title }}</h1>
                </div>
                <Button as-child>
                    <Link :href="page_setting.action">
                        <Edit class="mr-2 h-4 w-4" />
                        Edit User
                    </Link>
                </Button>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <!-- User Information -->
                <Card>
                    <CardHeader>
                        <CardTitle>User Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <p class="text-sm text-muted-foreground">Name</p>
                            <p class="font-medium">{{ page_data.user.data.name }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Email</p>
                            <p class="font-medium">{{ page_data.user.data.email }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Status</p>
                            <Badge :variant="page_data.user.data.is_active ? 'success' : 'destructive'">
                                {{ page_data.user.data.is_active ? 'Active' : 'Inactive' }}
                            </Badge>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Email Verified</p>
                            <Badge :variant="page_data.user.data.email_verified_at ? 'success' : 'warning'">
                                {{ page_data.user.data.email_verified_at ? 'Verified' : 'Not Verified' }}
                            </Badge>
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Created At</p>
                            <p class="font-medium">{{ new Date(page_data.user.data.created_at).toLocaleString() }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Roles -->
                <Card>
                    <CardHeader>
                        <CardTitle>Roles</CardTitle>
                        <CardDescription>Assigned roles for this user</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-wrap gap-2" v-if="page_data.user.data.roles && page_data.user.data.roles.length > 0">
                            <Badge v-for="role in page_data.user.data.roles" :key="role.id" variant="secondary">
                                {{ role.name }}
                            </Badge>
                        </div>
                        <p v-else class="text-muted-foreground">No roles assigned</p>
                    </CardContent>
                </Card>

                <!-- Permissions -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <CardTitle>Direct Permissions</CardTitle>
                        <CardDescription>Permissions assigned directly to this user</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-wrap gap-2" v-if="page_data.user.data.permissions && page_data.user.data.permissions.length > 0">
                            <Badge v-for="permission in page_data.user.data.permissions" :key="permission.id" variant="outline">
                                {{ permission.name }}
                            </Badge>
                        </div>
                        <p v-else class="text-muted-foreground">No direct permissions assigned</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
