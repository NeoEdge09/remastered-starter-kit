<script setup lang="ts">
import ToastNotification from '@/components/ToastNotification.vue';
import { AlertDialog } from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { Select } from '@/components/ui/select';
import { SortableTableHead, Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useDataTable, type SortDirection } from '@/composables/useDataTable';
import { usePermission } from '@/composables/usePermission';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, PaginatedData, Permission, PermissionGroup } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    page_setting: {
        title: string;
        subtitle: string;
        breadcrumbs: BreadcrumbItem[];
    };
    page_data: {
        permissions: PaginatedData<Permission>;
        permissionGroups: PermissionGroup[];
        filters: {
            search?: string;
            group?: string;
            sort?: string;
            direction?: SortDirection;
            per_page?: string;
        };
    };
}>();

const { can } = usePermission();

const { filters, sort, toggleSort, hasActiveFilters, clearFilters, perPage } = useDataTable({
    routeName: 'admin.permissions.index',
    filters: {
        search: props.page_data.filters.search,
        group: props.page_data.filters.group,
    },
    sortColumn: props.page_data.filters.sort,
    sortDirection: props.page_data.filters.direction,
    perPage: Number(props.page_data.filters.per_page) || 15,
});

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
const groupOptions = [
    { value: '', label: 'All Groups' },
    ...props.page_data.permissionGroups.map((g) => ({ value: g.id.toString(), label: g.name })),
];

const deletePermission = ref<Permission | null>(null);
const showDeleteDialog = ref(false);

const confirmDelete = () => {
    if (deletePermission.value) {
        router.delete(route('admin.permissions.destroy', { permission: deletePermission.value.id }), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                deletePermission.value = null;
            },
        });
    }
};

const openDeleteDialog = (permission: Permission) => {
    deletePermission.value = permission;
    showDeleteDialog.value = true;
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
                            <Button v-if="can('permission-group.create')" variant="outline" as-child>
                                <Link :href="route('admin.permission-groups.index')"> Manage Groups </Link>
                            </Button>
                            <Button v-if="can('permission.create')" as-child>
                                <Link :href="route('admin.permissions.create')">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Permission
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Filters -->
                    <div class="mb-4 flex flex-wrap items-center gap-4">
                        <div class="w-32 space-y-2">
                            <Select v-model="perPageString" :options="perPageOptions" />
                        </div>
                        <div class="relative min-w-[200px] flex-1">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="filters.search" placeholder="Search permissions..." class="pl-9" />
                        </div>
                        <Select v-model="filters.group" :options="groupOptions" placeholder="Filter by group" class="w-[200px]" />
                        <Button v-if="hasActiveFilters" variant="ghost" size="sm" @click="clearFilters">
                            <X class="mr-1 h-4 w-4" />
                            Clear
                        </Button>
                    </div>

                    <!-- Table -->
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <SortableTableHead column="name" :sort-column="sort.column" :sort-direction="sort.direction" @sort="toggleSort">
                                    Name
                                </SortableTableHead>
                                <TableHead>Description</TableHead>
                                <TableHead>Group</TableHead>
                                <SortableTableHead column="created_at" :sort-column="sort.column" :sort-direction="sort.direction" @sort="toggleSort">
                                    Created
                                </SortableTableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableEmpty v-if="page_data.permissions.data.length === 0" :colspan="5"> No permissions found. </TableEmpty>
                            <TableRow v-for="permission in page_data.permissions.data" :key="permission.id">
                                <TableCell class="font-mono text-sm font-medium">
                                    {{ permission.name }}
                                </TableCell>
                                <TableCell>
                                    {{ permission.description || '-' }}
                                </TableCell>
                                <TableCell>
                                    <Badge v-if="permission.group" variant="secondary">
                                        {{ permission.group.name }}
                                    </Badge>
                                    <span v-else class="text-muted-foreground">Uncategorized</span>
                                </TableCell>
                                <TableCell>
                                    {{ new Date(permission.created_at).toLocaleDateString() }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button v-if="can('permission.edit')" variant="ghost" size="icon" as-child>
                                            <Link :href="route('admin.permissions.edit', { permission: permission.id })">
                                                <Edit class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button v-if="can('permission.delete')" variant="ghost" size="icon" @click="openDeleteDialog(permission)">
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <Pagination :data="page_data.permissions" />
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog
            v-model:open="showDeleteDialog"
            title="Delete Permission"
            description="Are you sure you want to delete this permission? This may affect roles and users that have this permission."
            confirm-text="Delete"
            variant="destructive"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
