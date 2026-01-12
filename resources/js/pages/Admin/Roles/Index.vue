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
import type { BreadcrumbItem, PaginatedData, Role } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps<{
    page_setting: {
        title: string;
        subtitle: string;
        breadcrumbs: BreadcrumbItem[];
    };
    page_data: {
        roles: PaginatedData<Role>;
        filters: {
            search?: string;
            sort?: string;
            direction?: SortDirection;
        };
    };
}>();

const { can } = usePermission();

const { filters, sort, toggleSort, hasActiveFilters, clearFilters } = useDataTable({
    routeName: 'admin.roles.index',
    filters: {
        search: props.page_data.filters.search,
    },
    sortColumn: props.page_data.filters.sort,
    sortDirection: props.page_data.filters.direction,
});

const deleteRole = ref<Role | null>(null);
const showDeleteDialog = ref(false);

const confirmDelete = () => {
    if (deleteRole.value) {
        router.delete(route('admin.roles.destroy', { role: deleteRole.value.id }), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                deleteRole.value = null;
            },
        });
    }
};

const openDeleteDialog = (role: Role) => {
    deleteRole.value = role;
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
                        <Button v-if="can('role.create')" as-child>
                            <Link :href="route('admin.roles.create')">
                                <Plus class="mr-2 h-4 w-4" />
                                Add Role
                            </Link>
                        </Button>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Filters -->
                    <div class="mb-4 flex items-center gap-4">
                        <div class="relative max-w-sm flex-1">
                            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input v-model="filters.search" placeholder="Search roles..." class="pl-9" />
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
                                <SortableTableHead column="name" :sort-column="sort.column" :sort-direction="sort.direction" @sort="toggleSort">
                                    Name
                                </SortableTableHead>
                                <TableHead>Permissions</TableHead>
                                <SortableTableHead column="created_at" :sort-column="sort.column" :sort-direction="sort.direction" @sort="toggleSort">
                                    Created
                                </SortableTableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableEmpty v-if="page_data.roles.data.length === 0" :colspan="4"> No roles found. </TableEmpty>
                            <TableRow v-for="role in page_data.roles.data" :key="role.id">
                                <TableCell class="font-medium">
                                    {{ role.name }}
                                    <Badge v-if="role.name === 'super-admin'" class="ml-2" variant="default"> System </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary"> {{ role.permissions_count || 0 }} permissions </Badge>
                                </TableCell>
                                <TableCell>
                                    {{ new Date(role.created_at).toLocaleDateString() }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button v-if="can('role.edit')" variant="ghost" size="icon" as-child>
                                            <Link :href="route('admin.roles.edit', { role: role.id })">
                                                <Edit class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            v-if="can('role.delete') && role.name !== 'super-admin'"
                                            variant="ghost"
                                            size="icon"
                                            @click="openDeleteDialog(role)"
                                        >
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <Pagination :data="page_data.roles" />
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog
            v-model:open="showDeleteDialog"
            title="Delete Role"
            description="Are you sure you want to delete this role? Users with this role will lose its permissions."
            confirm-text="Delete"
            variant="destructive"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
