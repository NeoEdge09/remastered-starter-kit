<script setup lang="ts">
import ToastNotification from '@/components/ToastNotification.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Clock, FileText, Trash2, User } from 'lucide-vue-next';

interface ActivitySubject {
    id: number;
    name?: string;
    [key: string]: unknown;
}

interface ActivityCauser {
    id: number;
    name: string;
    email: string;
}

interface ActivityLog {
    id: number;
    log_name: string;
    description: string;
    subject_type: string | null;
    subject_id: number | null;
    subject: ActivitySubject | null;
    causer_type: string | null;
    causer_id: number | null;
    causer: ActivityCauser | null;
    event: string | null;
    properties: {
        attributes?: Record<string, unknown>;
        old?: Record<string, unknown>;
        [key: string]: unknown;
    };
    batch_uuid: string | null;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    page_setting: {
        title: string;
        breadcrumbs: BreadcrumbItem[];
        back_link: string;
    };
    page_data: {
        activity: ActivityLog;
    };
}>();

const getEventBadgeVariant = (event: string | null) => {
    switch (event) {
        case 'created':
            return 'default';
        case 'updated':
            return 'secondary';
        case 'deleted':
            return 'destructive';
        case 'login':
            return 'default';
        case 'logout':
            return 'outline';
        case 'login_failed':
            return 'destructive';
        case 'password_reset':
            return 'secondary';
        case 'email_verified':
            return 'default';
        default:
            return 'outline';
    }
};

const getEventLabel = (event: string | null) => {
    switch (event) {
        case 'created':
            return 'Created';
        case 'updated':
            return 'Updated';
        case 'deleted':
            return 'Deleted';
        case 'login':
            return 'Login';
        case 'logout':
            return 'Logout';
        case 'login_failed':
            return 'Login Failed';
        case 'password_reset':
            return 'Password Reset';
        case 'email_verified':
            return 'Email Verified';
        default:
            return event ? event.charAt(0).toUpperCase() + event.slice(1) : 'Unknown';
    }
};

const isAuthEvent = () => {
    return ['login', 'logout', 'login_failed', 'password_reset', 'email_verified'].includes(props.page_data.activity.event || '');
};

const isCrudEvent = () => {
    return ['created', 'updated', 'deleted'].includes(props.page_data.activity.event || '');
};

const getSubjectTypeName = (subjectType: string | null) => {
    if (!subjectType) return 'Unknown';
    const parts = subjectType.split('\\');
    return parts[parts.length - 1];
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};

const formatValue = (value: unknown): string => {
    if (value === null || value === undefined) return 'null';
    if (typeof value === 'boolean') return value ? 'true' : 'false';
    if (typeof value === 'object') return JSON.stringify(value, null, 2);
    return String(value);
};

const getChangedFields = () => {
    const changes: Array<{ field: string; old: unknown; new: unknown }> = [];
    const attributes = props.page_data.activity.properties.attributes || {};
    const oldValues = props.page_data.activity.properties.old || {};

    for (const key of Object.keys(attributes)) {
        if (key === 'updated_at' || key === 'created_at') continue;

        const oldValue = oldValues[key];
        const newValue = attributes[key];

        if (JSON.stringify(oldValue) !== JSON.stringify(newValue)) {
            changes.push({
                field: key,
                old: oldValue,
                new: newValue,
            });
        }
    }

    return changes;
};

const deleteActivity = () => {
    if (confirm('Are you sure you want to delete this activity log entry?')) {
        router.delete(route('admin.activity-logs.destroy', { activity_log: props.page_data.activity.id }));
    }
};
</script>

<template>
    <Head :title="page_setting.title" />
    <ToastNotification />

    <AppLayout :breadcrumbs="page_setting.breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="page_setting.back_link">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <h1 class="text-2xl font-bold">{{ page_setting.title }}</h1>
                </div>
                <Button variant="destructive" size="sm" @click="deleteActivity">
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete
                </Button>
            </div>

            <div class="grid gap-4 lg:grid-cols-3">
                <!-- Main Details -->
                <div class="space-y-4 lg:col-span-2">
                    <!-- Description Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <FileText class="h-5 w-5" />
                                Activity Details
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="rounded-lg bg-muted p-4">
                                <p class="text-lg">{{ page_data.activity.description }}</p>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Event Type</p>
                                    <Badge :variant="getEventBadgeVariant(page_data.activity.event)" class="mt-1">
                                        {{ getEventLabel(page_data.activity.event) }}
                                    </Badge>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-muted-foreground">Log Name</p>
                                    <p class="mt-1 capitalize">{{ page_data.activity.log_name }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Auth Event Details Card -->
                    <Card v-if="isAuthEvent()">
                        <CardHeader>
                            <CardTitle>Authentication Details</CardTitle>
                            <CardDescription>Information about this authentication event</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="rounded-lg border">
                                <table class="w-full">
                                    <tbody>
                                        <tr v-if="page_data.activity.properties.ip_address" class="border-b">
                                            <td class="p-3 font-medium">IP Address</td>
                                            <td class="p-3">
                                                <code class="rounded bg-muted px-2 py-1 text-sm">{{ page_data.activity.properties.ip_address }}</code>
                                            </td>
                                        </tr>
                                        <tr v-if="page_data.activity.properties.user_agent" class="border-b">
                                            <td class="p-3 font-medium">User Agent</td>
                                            <td class="p-3 text-sm text-muted-foreground">{{ page_data.activity.properties.user_agent }}</td>
                                        </tr>
                                        <tr v-if="page_data.activity.properties.email" class="border-b">
                                            <td class="p-3 font-medium">Email Attempted</td>
                                            <td class="p-3">{{ page_data.activity.properties.email }}</td>
                                        </tr>
                                        <tr v-for="(value, key) in page_data.activity.properties" :key="key" class="border-b last:border-b-0">
                                            <template v-if="!['ip_address', 'user_agent', 'email', 'attributes', 'old'].includes(String(key))">
                                                <td class="p-3 font-medium capitalize">{{ String(key).replace(/_/g, ' ') }}</td>
                                                <td class="p-3">
                                                    <pre class="whitespace-pre-wrap text-sm">{{ formatValue(value) }}</pre>
                                                </td>
                                            </template>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Changes Card (for update events) -->
                    <Card v-if="page_data.activity.event === 'updated' && getChangedFields().length > 0">
                        <CardHeader>
                            <CardTitle>Changes Made</CardTitle>
                            <CardDescription>Fields that were modified in this update</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div v-for="change in getChangedFields()" :key="change.field" class="rounded-lg border p-4">
                                    <p class="mb-2 font-medium capitalize">{{ change.field.replace(/_/g, ' ') }}</p>
                                    <div class="grid gap-2 sm:grid-cols-2">
                                        <div class="rounded bg-red-50 p-2 dark:bg-red-950">
                                            <p class="text-xs font-medium text-red-600 dark:text-red-400">Old Value</p>
                                            <pre class="mt-1 whitespace-pre-wrap text-sm">{{ formatValue(change.old) }}</pre>
                                        </div>
                                        <div class="rounded bg-green-50 p-2 dark:bg-green-950">
                                            <p class="text-xs font-medium text-green-600 dark:text-green-400">New Value</p>
                                            <pre class="mt-1 whitespace-pre-wrap text-sm">{{ formatValue(change.new) }}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Properties Card (for created events) -->
                    <Card v-if="page_data.activity.event === 'created' && page_data.activity.properties.attributes">
                        <CardHeader>
                            <CardTitle>Created With Values</CardTitle>
                            <CardDescription>Initial values when the record was created</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="rounded-lg border">
                                <table class="w-full">
                                    <tbody>
                                        <tr
                                            v-for="(value, key) in page_data.activity.properties.attributes"
                                            :key="key"
                                            class="border-b last:border-b-0"
                                        >
                                            <td class="p-3 font-medium capitalize">{{ String(key).replace(/_/g, ' ') }}</td>
                                            <td class="p-3">
                                                <pre class="whitespace-pre-wrap text-sm">{{ formatValue(value) }}</pre>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Raw Properties Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Raw Properties</CardTitle>
                            <CardDescription>Complete metadata stored with this activity</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <pre class="max-h-64 overflow-auto rounded-lg bg-muted p-4 text-sm">{{
                                JSON.stringify(page_data.activity.properties, null, 2)
                            }}</pre>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-4">
                    <!-- Time Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-base">
                                <Clock class="h-4 w-4" />
                                Timestamp
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div class="flex items-center gap-2 text-sm">
                                <Calendar class="h-4 w-4 text-muted-foreground" />
                                <span>{{ formatDate(page_data.activity.created_at) }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Causer Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-base">
                                <User class="h-4 w-4" />
                                Performed By
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="page_data.activity.causer" class="space-y-2">
                                <p class="font-medium">{{ page_data.activity.causer.name }}</p>
                                <p class="text-sm text-muted-foreground">{{ page_data.activity.causer.email }}</p>
                                <Badge variant="outline">User #{{ page_data.activity.causer.id }}</Badge>
                            </div>
                            <div v-else class="text-muted-foreground">
                                <p>System Action</p>
                                <p class="text-sm">This action was performed automatically</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Subject Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base">Subject</CardTitle>
                            <CardDescription>The model this activity relates to</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-2">
                            <div class="flex items-center gap-2">
                                <Badge variant="outline">{{ getSubjectTypeName(page_data.activity.subject_type) }}</Badge>
                                <span v-if="page_data.activity.subject_id" class="text-sm text-muted-foreground"
                                    >#{{ page_data.activity.subject_id }}</span
                                >
                            </div>
                            <div v-if="page_data.activity.subject" class="mt-2 rounded-lg bg-muted p-3">
                                <p class="text-sm font-medium">{{ page_data.activity.subject.name || `ID: ${page_data.activity.subject.id}` }}</p>
                            </div>
                            <p v-else-if="page_data.activity.event === 'deleted'" class="text-sm text-muted-foreground">
                                This record has been deleted
                            </p>
                        </CardContent>
                    </Card>

                    <!-- Batch UUID Card (if exists) -->
                    <Card v-if="page_data.activity.batch_uuid">
                        <CardHeader>
                            <CardTitle class="text-base">Batch ID</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <code class="break-all rounded bg-muted px-2 py-1 text-xs">{{ page_data.activity.batch_uuid }}</code>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
