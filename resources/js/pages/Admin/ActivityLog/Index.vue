<script setup lang="ts">
import ToastNotification from '@/components/ToastNotification.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pagination } from '@/components/ui/pagination';
import { Select } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useDataTable } from '@/composables/useDataTable';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Activity, BreadcrumbItem, PaginatedData } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Activity as ActivityIcon, Calendar, Download, Eye, Filter, RefreshCw, Search, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface User {
    id: number;
    name: string;
}

const props = defineProps<{
    page_setting: {
        title: string;
        subtitle: string;
        breadcrumbs: BreadcrumbItem[];
    };
    page_data: {
        activities: PaginatedData<Activity>;
        filters: {
            search: string;
            sort: string;
            direction: string;
            log_name?: string;
            event?: string;
            causer_id?: string;
            per_page?: string;
        };
        logNames: string[];
        events: string[];
        users: User[];
        stats: {
            today: number;
            thisWeek: number;
            thisMonth: number;
            total: number;
        };
    };
}>();

const { filters, clearFilters, perPage } = useDataTable({
    routeName: 'admin.activity-logs.index',
    filters: {
        search: props.page_data.filters.search || '',
        log_name: props.page_data.filters.log_name || '',
        event: props.page_data.filters.event || '',
        causer_id: props.page_data.filters.causer_id || '',
    },
    perPage: Number(props.page_data.filters.per_page) || 15,
});

const showClearDialog = ref(false);
const clearConfirmation = ref('');

const clearForm = useForm({
    confirm: '',
});

const logNameOptions = computed(() => [
    { value: '', label: 'All Logs' },
    ...props.page_data.logNames.map((name) => ({ value: name, label: name.charAt(0).toUpperCase() + name.slice(1) })),
]);

const eventOptions = computed(() => [
    { value: '', label: 'All Events' },
    ...props.page_data.events.map((event) => ({ value: event, label: getEventLabel(event) })),
]);

const userOptions = computed(() => [
    { value: '', label: 'All Users' },
    ...props.page_data.users.map((user) => ({ value: user.id.toString(), label: user.name })),
]);

const perPageOptions = [
    { value: '15', label: '15 per page' },
    { value: '25', label: '25 per page' },
    { value: '50', label: '50 per page' },
    { value: '100', label: '100 per page' },
];

const perPageString = computed({
    get: () => perPage.value.toString(),
    set: (val) => (perPage.value = Number(val)),
});

const hasActiveFilters = computed(() => {
    return filters.value.search || filters.value.log_name || filters.value.event || filters.value.causer_id;
});

const resetFilters = () => {
    clearFilters();
};

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

const getSubjectTypeName = (subjectType: string | null) => {
    if (!subjectType) return 'Unknown';
    const parts = subjectType.split('\\');
    return parts[parts.length - 1];
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};

const formatRelativeTime = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now.getTime() - date.getTime();

    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;

    return formatDate(dateString);
};

const deleteActivity = (activity: Activity) => {
    if (confirm('Are you sure you want to delete this activity log entry?')) {
        router.delete(route('admin.activity-logs.destroy', { activity_log: activity.id }));
    }
};

const clearAllLogs = () => {
    clearForm.confirm = clearConfirmation.value;
    clearForm.post(route('admin.activity-logs.clear'), {
        onSuccess: () => {
            showClearDialog.value = false;
            clearConfirmation.value = '';
        },
    });
};

const exportLogs = () => {
    const params = new URLSearchParams();
    if (filters.value.log_name) params.append('log_name', filters.value.log_name);
    if (filters.value.event) params.append('event', filters.value.event);
    if (filters.value.causer_id) params.append('causer_id', filters.value.causer_id);

    window.location.href = route('admin.activity-logs.export') + '?' + params.toString();
};
</script>

<template>
    <Head :title="page_setting.title" />
    <ToastNotification />

    <AppLayout :breadcrumbs="page_setting.breadcrumbs">
        <div class="flex h-full min-w-0 flex-1 flex-col gap-4 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">{{ page_setting.title }}</h1>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="exportLogs">
                        <Download class="mr-2 h-4 w-4" />
                        Export
                    </Button>

                    <Dialog v-model:open="showClearDialog">
                        <DialogTrigger as-child>
                            <Button variant="destructive" size="sm">
                                <Trash2 class="mr-2 h-4 w-4" />
                                Clear All
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>Clear All Activity Logs</DialogTitle>
                                <DialogDescription>
                                    This action cannot be undone. All activity log entries will be permanently deleted.
                                </DialogDescription>
                            </DialogHeader>
                            <div class="space-y-4 py-4">
                                <p class="text-sm text-muted-foreground">Type <strong>DELETE_ALL</strong> to confirm:</p>
                                <Input v-model="clearConfirmation" placeholder="DELETE_ALL" />
                            </div>
                            <DialogFooter>
                                <Button variant="outline" @click="showClearDialog = false">Cancel</Button>
                                <Button variant="destructive" :disabled="clearConfirmation !== 'DELETE_ALL'" @click="clearAllLogs">
                                    Clear All Logs
                                </Button>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Today</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ page_data.stats.today }}</div>
                        <p class="text-xs text-muted-foreground">activities logged</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">This Week</CardTitle>
                        <ActivityIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ page_data.stats.thisWeek }}</div>
                        <p class="text-xs text-muted-foreground">activities logged</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">This Month</CardTitle>
                        <RefreshCw class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ page_data.stats.thisMonth }}</div>
                        <p class="text-xs text-muted-foreground">activities logged</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total</CardTitle>
                        <ActivityIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ page_data.stats.total }}</div>
                        <p class="text-xs text-muted-foreground">all time</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Filter class="h-4 w-4" />
                        Filters
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 space-y-2">
                            <Label for="search">Search</Label>
                            <div class="relative">
                                <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input id="search" v-model="filters.search" type="text" placeholder="Search descriptions..." class="pl-8" />
                            </div>
                        </div>
                        <div class="w-40 space-y-2">
                            <Label>Log Name</Label>
                            <Select v-model="filters.log_name" :options="logNameOptions" />
                        </div>
                        <div class="w-32 space-y-2">
                            <Label>Event</Label>
                            <Select v-model="filters.event" :options="eventOptions" />
                        </div>
                        <div class="w-40 space-y-2">
                            <Label>User</Label>
                            <Select v-model="filters.causer_id" :options="userOptions" />
                        </div>
                        <div class="w-32 space-y-2">
                            <Label>Per Page</Label>
                            <Select v-model="perPageString" :options="perPageOptions" />
                        </div>
                        <div class="flex gap-2">
                            <Button v-if="hasActiveFilters" variant="outline" @click="resetFilters">
                                <X class="mr-2 h-4 w-4" />
                                Clear
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Activity Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Activities</CardTitle>
                    <CardDescription>
                        Showing {{ page_data.activities.data.length }} of {{ page_data.activities.meta.total }} activity log entries
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <Table class="whitespace-nowrap">
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-[180px]">When</TableHead>
                                <TableHead class="w-[100px]">Event</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead class="w-[100px]">Subject</TableHead>
                                <TableHead class="w-[120px]">User</TableHead>
                                <TableHead class="w-[100px] text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="activity in page_data.activities.data" :key="activity.id">
                                <TableCell class="font-medium">
                                    <div class="flex flex-col">
                                        <span class="text-sm">{{ formatRelativeTime(activity.created_at) }}</span>
                                        <span class="text-xs text-muted-foreground">{{ formatDate(activity.created_at) }}</span>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="getEventBadgeVariant(activity.event)">
                                        {{ getEventLabel(activity.event) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div class="max-w-md truncate" :title="activity.description">
                                        {{ activity.description }}
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">
                                        {{ getSubjectTypeName(activity.subject_type) }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <div v-if="activity.causer" class="flex items-center gap-2">
                                        <span class="text-sm">{{ activity.causer.name }}</span>
                                    </div>
                                    <span v-else class="text-sm text-muted-foreground">System</span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="ghost" size="icon" as-child>
                                            <Link :href="route('admin.activity-logs.show', { activity_log: activity.id })">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button variant="ghost" size="icon" @click="deleteActivity(activity)">
                                            <Trash2 class="h-4 w-4 text-red-500" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="page_data.activities.data.length === 0">
                                <TableCell colspan="6" class="py-8 text-center text-muted-foreground"> No activity logs found. </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <Pagination :data="page_data.activities" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
