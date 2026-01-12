<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Activity, ArrowUpRight, CreditCard, DollarSign, Users } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<SharedData>();
const user = computed(() => page.props.auth.user);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const stats = [
    {
        title: 'Total Revenue',
        value: '$45,231.89',
        change: '+20.1%',
        trend: 'up',
        icon: DollarSign,
    },
    {
        title: 'Subscriptions',
        value: '+2,350',
        change: '+180.1%',
        trend: 'up',
        icon: Users,
    },
    {
        title: 'Sales',
        value: '+12,234',
        change: '+19%',
        trend: 'up',
        icon: CreditCard,
    },
    {
        title: 'Active Now',
        value: '+573',
        change: '+201',
        trend: 'up',
        icon: Activity,
    },
];

const recentActivity = [
    { user: 'John Doe', action: 'Created new user', time: '2 minutes ago' },
    { user: 'Jane Smith', action: 'Updated role permissions', time: '15 minutes ago' },
    { user: 'Mike Johnson', action: 'Deleted activity log', time: '1 hour ago' },
    { user: 'Sarah Williams', action: 'Created new menu item', time: '2 hours ago' },
    { user: 'System', action: 'Route access scan completed', time: '3 hours ago' },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full min-w-0 flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                <p class="text-muted-foreground">Welcome back, {{ user?.name }}</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card v-for="stat in stats" :key="stat.title">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">{{ stat.title }}</CardTitle>
                        <component :is="stat.icon" class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stat.value }}</div>
                        <p class="text-xs text-muted-foreground">
                            <span :class="stat.trend === 'up' ? 'text-green-600' : 'text-red-600'">{{ stat.change }}</span>
                            from last month
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-7">
                <Card class="col-span-4">
                    <CardHeader>
                        <CardTitle>Overview</CardTitle>
                        <CardDescription>Monthly performance metrics</CardDescription>
                    </CardHeader>
                    <CardContent class="pl-2">
                        <div class="flex h-[300px] w-full items-center justify-center rounded-md border bg-muted/50">
                            <p class="text-sm text-muted-foreground">Chart placeholder</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="col-span-3">
                    <CardHeader>
                        <CardTitle>Recent Activity</CardTitle>
                        <CardDescription>Latest system activities</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="(activity, index) in recentActivity" :key="index" class="flex items-start gap-4">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-muted">
                                    <Activity class="h-4 w-4 text-muted-foreground" />
                                </div>
                                <div class="flex-1 space-y-1">
                                    <p class="text-sm font-medium leading-none">{{ activity.user }}</p>
                                    <p class="text-sm text-muted-foreground">{{ activity.action }}</p>
                                    <p class="text-xs text-muted-foreground">{{ activity.time }}</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Quick Actions</CardTitle>
                        <CardDescription>Common administrative tasks</CardDescription>
                    </CardHeader>
                    <CardContent class="grid gap-2">
                        <button class="flex items-center justify-between rounded-lg border p-3 text-left transition-colors hover:bg-muted">
                            <span class="text-sm font-medium">Scan Routes</span>
                            <ArrowUpRight class="h-4 w-4 text-muted-foreground" />
                        </button>
                        <button class="flex items-center justify-between rounded-lg border p-3 text-left transition-colors hover:bg-muted">
                            <span class="text-sm font-medium">Manage Users</span>
                            <ArrowUpRight class="h-4 w-4 text-muted-foreground" />
                        </button>
                        <button class="flex items-center justify-between rounded-lg border p-3 text-left transition-colors hover:bg-muted">
                            <span class="text-sm font-medium">View Activity Logs</span>
                            <ArrowUpRight class="h-4 w-4 text-muted-foreground" />
                        </button>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>System Status</CardTitle>
                        <CardDescription>Current system information</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Application Version</span>
                                <span class="text-sm font-medium">v1.0.0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Laravel Version</span>
                                <span class="text-sm font-medium">12.0</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Total Users</span>
                                <span class="text-sm font-medium">2,350</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Active Sessions</span>
                                <span class="text-sm font-medium">573</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
