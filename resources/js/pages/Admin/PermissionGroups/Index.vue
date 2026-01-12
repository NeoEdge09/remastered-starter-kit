<script setup lang="ts">
import ToastNotification from '@/components/ToastNotification.vue';
import { AlertDialog } from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Pagination } from '@/components/ui/pagination';
import { SortableTableHead, Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useDataTable, type SortDirection } from '@/composables/useDataTable';
import { usePermission } from '@/composables/usePermission';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, PaginatedData, PermissionGroup } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Edit, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    page_setting: {
        title: string;
        subtitle: string;
        breadcrumbs: BreadcrumbItem[];
    };
    page_data: {
        permissionGroups: PaginatedData<PermissionGroup>;
        filters: {
            search?: string;
            sort?: string;
            direction?: SortDirection;
        };
    };
}>();

const { can } = usePermission();

const { filters, sort, toggleSort, hasActiveFilters, clearFilters } = useDataTable({
    routeName: 'admin.permission-groups.index',
    filters: {
        search: props.page_data.filters.search,
    },
    sortColumn: props.page_data.filters.sort,
    sortDirection: props.page_data.filters.direction,
});

const deleteGroup = ref<PermissionGroup | null>(null);
const showDeleteDialog = ref(false);

const confirmDelete = () => {
    if (deleteGroup.value) {
        router.delete(route('admin.permission-groups.destroy', { permission_group: deleteGroup.value.id }), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                deleteGroup.value = null;
            },
        });
    }
};

const openDeleteDialog = (group: PermissionGroup) => {
    deleteGroup.value = group;
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
                        <div class="flex items-center gap-4">
                            <Button variant="ghost" size="icon" as-child>
                                <Link :href="route('admin.permissions.index')">
                                    <ArrowLeft class="h-4 w-4" />
                                </Link>
                            </Button>
                            <div>
                                <CardTitle>{{ page_setting.title }}</CardTitle>
                                <CardDescription>{{ page_setting.subtitle }}</CardDescription>
                            </div>
                        </div>
                        <Button v-if="can('permission-group.create')" as-child>
                            <Link :href="route('admin.permission-groups.create')">
                                <Plus class="mr-2 h-4 w-4" />
                                Add Group
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Filters -->
                    <div class="mb-4 flex items-center gap-4">
                        <div class="relative max-w-sm flex-1">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="filters.search" placeholder="Search groups..." class="pl-9" />
                        </div>
                        <Button v-if="hasActiveFilters" variant="ghost" size="sm" @click="clearFilters">
                            <X class="mr-1 h-4 w-4" />
                            Clear
                        </Button>
                    </div>

                    <!-- Table -->
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <SortableTableHead column="order" :sort-column="sort.column" :sort-direction="sort.direction" @sort="toggleSort">
                                    Order
                                </SortableTableHead>
                                <SortableTableHead column="name" :sort-column="sort.column" :sort-direction="sort.direction" @sort="toggleSort">
                                    Name
                                </SortableTableHead>
                                <TableHead>Description</TableHead>
                                <TableHead>Permissions</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableEmpty v-if="page_data.permissionGroups.data.length === 0" :colspan="5"> No permission groups found. </TableEmpty>
                            <TableRow v-for="group in page_data.permissionGroups.data" :key="group.id">
                                <TableCell>{{ group.order }}</TableCell>
                                <TableCell class="font-medium">{{ group.name }}</TableCell>
                                <TableCell>{{ group.description || '-' }}</TableCell>
                                <TableCell>
                                    <Badge variant="secondary"> {{ group.permissions_count || 0 }} permissions </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button v-if="can('permission-group.edit')" variant="ghost" size="icon" as-child>
                                            <Link :href="route('admin.permission-groups.edit', { permission_group: group.id })">
                                                <Edit class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            v-if="can('permission-group.delete')"
                                            variant="ghost"
                                            size="icon"
                                            :disabled="(group.permissions_count || 0) > 0"
                                            @click="openDeleteDialog(group)"
                                        >
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <Pagination :data="page_data.permissionGroups" />
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog
            v-model:open="showDeleteDialog"
            title="Delete Permission Group"
            description="Are you sure you want to delete this permission group?"
            confirm-text="Delete"
            variant="destructive"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
