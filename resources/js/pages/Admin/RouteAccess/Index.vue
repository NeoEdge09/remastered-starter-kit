<script setup lang="ts">
import ToastNotification from '@/components/ToastNotification.vue';
import { AlertDialog } from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { Select } from '@/components/ui/select';
import { SortableTableHead, Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useDataTable, type SortDirection } from '@/composables/useDataTable';
import { usePermission } from '@/composables/usePermission';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, PaginatedData, RouteAccess } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Plus, RefreshCw, Search, Settings, Shield, ShieldOff, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    page_setting: {
        title: string;
        subtitle: string;
        breadcrumbs: BreadcrumbItem[];
    };
    page_data: {
        routeAccesses: PaginatedData<RouteAccess>;
        filters: {
            search?: string;
            is_active?: string;
            is_public?: string;
            sort?: string;
            direction?: SortDirection;
        };
        unregisteredCount: number;
    };
}>();

const { can } = usePermission();

const { filters, sort, toggleSort, hasActiveFilters, clearFilters } = useDataTable({
    routeName: 'admin.route-accesses.index',
    filters: {
        search: props.page_data.filters.search,
        is_active: props.page_data.filters.is_active,
        is_public: props.page_data.filters.is_public,
    },
    sortColumn: props.page_data.filters.sort,
    sortDirection: props.page_data.filters.direction,
});

const statusOptions = [
    { value: '', label: 'All Status' },
    { value: 'true', label: 'Active' },
    { value: 'false', label: 'Inactive' },
];

const publicOptions = [
    { value: '', label: 'All Access' },
    { value: 'true', label: 'Public' },
    { value: 'false', label: 'Protected' },
];

// Selection
const selectedIds = ref<number[]>([]);
const selectAll = ref(false);

const toggleSelectAll = (checked: boolean | 'indeterminate') => {
    selectAll.value = checked === true;
    if (checked === true) {
        selectedIds.value = props.page_data.routeAccesses.data.map((r) => r.id);
    } else {
        selectedIds.value = [];
    }
};

const toggleSelect = (id: number) => {
    const index = selectedIds.value.indexOf(id);
    if (index === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(index, 1);
    }
    selectAll.value = selectedIds.value.length === props.page_data.routeAccesses.data.length;
};

const hasSelection = computed(() => selectedIds.value.length > 0);

// Dialogs
const deleteItem = ref<RouteAccess | null>(null);
const showDeleteDialog = ref(false);
const showScanDialog = ref(false);
const showBulkDeleteDialog = ref(false);
const scanning = ref(false);

const confirmDelete = () => {
    if (deleteItem.value) {
        router.delete(route('admin.route-accesses.destroy', { route_access: deleteItem.value.id }), {
            onSuccess: () => {
                deleteItem.value = null;
            },
        });
    }
};

const openDeleteDialog = (item: RouteAccess) => {
    deleteItem.value = item;
    showDeleteDialog.value = true;
};

const scanRoutes = () => {
    scanning.value = true;
    router.post(
        route('admin.route-accesses.scan'),
        {
            prefixes: [], // Empty = scan all routes
            auto_permission: true,
        },
        {
            onFinish: () => {
                scanning.value = false;
            },
        },
    );
};

const bulkAction = (action: string) => {
    if (!hasSelection.value) return;

    router.post(route('admin.route-accesses.bulk-update'), {
        ids: selectedIds.value,
        action: action,
    });
    selectedIds.value = [];
    selectAll.value = false;
};

const bulkDelete = () => {
    if (!hasSelection.value) return;

    router.post(
        route('admin.route-accesses.bulk-destroy'),
        {
            ids: selectedIds.value,
        },
        {
            onSuccess: () => {
                selectedIds.value = [];
                selectAll.value = false;
            },
        },
    );
};

const syncPermissions = () => {
    router.post(route('admin.route-accesses.sync-permissions'));
};

const getMethodBadgeVariant = (method: string | null) => {
    switch (method?.toUpperCase()) {
        case 'GET':
            return 'default';
        case 'POST':
            return 'secondary';
        case 'PUT':
        case 'PATCH':
            return 'outline';
        case 'DELETE':
            return 'destructive';
        default:
            return 'outline';
    }
};
</script>

<template>
    <Head :title="page_setting.title" />
    <ToastNotification />

    <AppLayout :breadcrumbs="page_setting.breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>{{ page_setting.title }}</CardTitle>
                            <CardDescription>{{ page_setting.subtitle }}</CardDescription>
                        </div>
                        <div class="flex gap-2">
                            <Button v-if="can('route-access.update')" variant="outline" @click="syncPermissions">
                                <Settings class="mr-2 h-4 w-4" />
                                Sync Permissions
                            </Button>
                            <Button v-if="can('route-access.create')" variant="outline" @click="showScanDialog = true">
                                <RefreshCw class="mr-2 h-4 w-4" />
                                Scan Routes
                                <Badge v-if="page_data.unregisteredCount > 0" variant="destructive" class="ml-2">
                                    {{ page_data.unregisteredCount }}
                                </Badge>
                            </Button>
                            <Button v-if="can('route-access.create')" as-child>
                                <Link :href="route('admin.route-accesses.create')">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Route
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Bulk Actions -->
                    <div v-if="hasSelection" class="mb-4 flex items-center gap-2 rounded-lg bg-muted p-2">
                        <span class="text-sm text-muted-foreground">{{ selectedIds.length }} selected</span>
                        <Button size="sm" variant="outline" @click="bulkAction('activate')">
                            <Shield class="mr-1 h-3 w-3" />
                            Activate
                        </Button>
                        <Button size="sm" variant="outline" @click="bulkAction('deactivate')">
                            <ShieldOff class="mr-1 h-3 w-3" />
                            Deactivate
                        </Button>
                        <Button size="sm" variant="outline" @click="bulkAction('make_public')"> Make Public </Button>
                        <Button size="sm" variant="outline" @click="bulkAction('make_protected')"> Make Protected </Button>
                        <Button size="sm" variant="destructive" @click="showBulkDeleteDialog = true">
                            <Trash2 class="mr-1 h-3 w-3" />
                            Delete
                        </Button>
                    </div>

                    <!-- Filters -->
                    <div class="mb-4 flex flex-wrap items-center gap-4">
                        <div class="relative min-w-[200px] flex-1">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="filters.search" placeholder="Search routes..." class="pl-9" />
                        </div>
                        <Select v-model="filters.is_active" :options="statusOptions" placeholder="Status" class="w-[140px]" />
                        <Select v-model="filters.is_public" :options="publicOptions" placeholder="Access" class="w-[140px]" />
                        <Button v-if="hasActiveFilters" variant="ghost" size="sm" @click="clearFilters">
                            <X class="mr-1 h-4 w-4" />
                            Clear
                        </Button>
                    </div>

                    <!-- Table -->
                    <div class="rounded-md border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[50px]">
                                        <Checkbox :checked="selectAll" @update:checked="toggleSelectAll" />
                                    </TableHead>
                                    <SortableTableHead
                                        column="route_name"
                                        :current-column="sort.column"
                                        :current-direction="sort.direction"
                                        @sort="toggleSort"
                                    >
                                        Route Name
                                    </SortableTableHead>
                                    <TableHead>URI</TableHead>
                                    <TableHead class="w-[80px]">Method</TableHead>
                                    <SortableTableHead
                                        column="permission_name"
                                        :current-column="sort.column"
                                        :current-direction="sort.direction"
                                        @sort="toggleSort"
                                    >
                                        Permission
                                    </SortableTableHead>
                                    <TableHead class="w-[100px]">Status</TableHead>
                                    <TableHead class="w-[100px]">Access</TableHead>
                                    <TableHead class="w-[100px] text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableEmpty v-if="page_data.routeAccesses.data.length === 0" :colspan="8" message="No route accesses found." />
                                <TableRow
                                    v-for="item in page_data.routeAccesses.data"
                                    :key="item.id"
                                    :class="{ 'bg-muted/50': selectedIds.includes(item.id) }"
                                >
                                    <TableCell>
                                        <Checkbox :checked="selectedIds.includes(item.id)" @update:checked="toggleSelect(item.id)" />
                                    </TableCell>
                                    <TableCell class="font-mono text-sm">{{ item.route_name }}</TableCell>
                                    <TableCell class="font-mono text-sm text-muted-foreground">{{ item.route_uri || '-' }}</TableCell>
                                    <TableCell>
                                        <Badge v-if="item.route_method" :variant="getMethodBadgeVariant(item.route_method)">
                                            {{ item.route_method }}
                                        </Badge>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </TableCell>
                                    <TableCell>
                                        <code v-if="item.permission_name" class="rounded bg-muted px-1 py-0.5 text-xs">
                                            {{ item.permission_name }}
                                        </code>
                                        <span v-else class="text-muted-foreground">-</span>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="item.is_active ? 'default' : 'secondary'">
                                            {{ item.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="item.is_public ? 'outline' : 'secondary'">
                                            {{ item.is_public ? 'Public' : 'Protected' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        <div class="flex justify-end gap-2">
                                            <Button v-if="can('route-access.update')" variant="ghost" size="icon" as-child>
                                                <Link :href="route('admin.route-accesses.edit', { route_access: item.id })">
                                                    <Edit class="h-4 w-4" />
                                                </Link>
                                            </Button>
                                            <Button v-if="can('route-access.delete')" variant="ghost" size="icon" @click="openDeleteDialog(item)">
                                                <Trash2 class="h-4 w-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        <Pagination :data="page_data.routeAccesses" />
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>

    <!-- Delete Dialog -->
    <AlertDialog
        v-model:open="showDeleteDialog"
        title="Delete Route Access"
        :description="`Are you sure you want to delete '${deleteItem?.route_name}'?`"
        confirm-text="Delete"
        variant="destructive"
        @confirm="confirmDelete"
    />

    <!-- Bulk Delete Dialog -->
    <AlertDialog
        v-model:open="showBulkDeleteDialog"
        title="Delete Selected Routes"
        :description="`Are you sure you want to delete ${selectedIds.length} route accesses?`"
        confirm-text="Delete"
        variant="destructive"
        @confirm="bulkDelete"
    />

    <!-- Scan Dialog -->
    <AlertDialog
        v-model:open="showScanDialog"
        title="Scan Routes"
        description="This will scan all routes and sync them to the database. New routes will be created with auto-generated permission names."
        confirm-text="Scan Routes"
        @confirm="scanRoutes"
    />
</template>
