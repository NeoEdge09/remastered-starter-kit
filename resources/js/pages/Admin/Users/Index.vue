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
import type { BreadcrumbItem, PaginatedData, Role, User } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Eye, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    page_setting: {
        title: string;
        subtitle: string;
        breadcrumbs: BreadcrumbItem[];
    };
    page_data: {
        users: PaginatedData<User>;
        roles: { data: Role[] };
        filters: {
            search?: string;
            status?: string;
            role?: string;
            sort?: string;
            direction?: SortDirection;
            per_page?: string;
        };
    };
}>();



const { can } = usePermission();

const { filters, sort, toggleSort, hasActiveFilters, clearFilters, perPage } = useDataTable({
    routeName: 'admin.users.index',
    filters: {
        search: props.page_data.filters.search,
        status: props.page_data.filters.status,
        role: props.page_data.filters.role,
    },
    sortColumn: props.page_data.filters.sort,
    sortDirection: props.page_data.filters.direction,
    perPage: Number(props.page_data.filters.per_page) || 15,
});

const statusOptions = [
    { value: '', label: 'All Status' },
    { value: 'active', label: 'Active' },
    { value: 'inactive', label: 'Inactive' },
];

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

const roleOptions = [{ value: '', label: 'All Roles' }, ...props.page_data.roles.data.map((r) => ({ value: r.id.toString(), label: r.name }))];

const deleteUser = ref<User | null>(null);
const showDeleteDialog = ref(false);

const confirmDelete = () => {
    if (deleteUser.value) {
        router.delete(route('admin.users.destroy', { user: deleteUser.value.id }), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                deleteUser.value = null;
            },
        });
    }
};

const openDeleteDialog = (user: User) => {
    deleteUser.value = user;
    showDeleteDialog.value = true;
};
</script>

<template>
    <Head :title="page_setting.title" />
    <ToastNotification />

    <AppLayout :breadcrumbs="page_setting.breadcrumbs">
        <div class="flex h-full min-w-0 flex-1 flex-col gap-4 p-4">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>{{ page_setting.title }}</CardTitle>
                            <CardDescription>{{ page_setting.subtitle }}</CardDescription>
                        </div>
                        <Button v-if="can('user.create')" as-child>
                            <Link :href="route('admin.users.create')">
                                <Plus class="mr-2 h-4 w-4" />
                                Add User
                            </Link>
                        </Button>
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
                            <Input v-model="filters.search" placeholder="Search users..." class="pl-9" />
                        </div>
                        <Select v-model="filters.status" :options="statusOptions" placeholder="Filter by status" class="w-[180px]" />
                        <Select v-model="filters.role" :options="roleOptions" placeholder="Filter by role" class="w-[180px]" />
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
                                <SortableTableHead column="email" :sort-column="sort.column" :sort-direction="sort.direction" @sort="toggleSort">
                                    Email
                                </SortableTableHead>
                                <TableHead>Roles</TableHead>
                                <SortableTableHead column="is_active" :sort-column="sort.column" :sort-direction="sort.direction" @sort="toggleSort">
                                    Status
                                </SortableTableHead>
                                <SortableTableHead column="created_at" :sort-column="sort.column" :sort-direction="sort.direction" @sort="toggleSort">
                                    Created
                                </SortableTableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableEmpty v-if="page_data.users.data.length === 0" :colspan="6"> No users found. </TableEmpty>
                            <TableRow v-for="user in page_data.users.data" :key="user.id">
                                <TableCell class="font-medium">{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-for="userRole in user.roles" :key="userRole.id" variant="secondary">
                                            {{ userRole.name }}
                                        </Badge>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="user.is_active ? 'success' : 'destructive'">
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button v-if="can('user.view')" variant="ghost" size="icon" as-child>
                                            <Link :href="route('admin.users.show', { user: user.id })">
                                                <Eye class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button v-if="can('user.edit')" variant="ghost" size="icon" as-child>
                                            <Link :href="route('admin.users.edit', { user: user.id })">
                                                <Edit class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button v-if="can('user.delete')" variant="ghost" size="icon" @click="openDeleteDialog(user)">
                                            <Trash2 class="h-4 w-4 text-destructive" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <Pagination :data="page_data.users" />
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog
            v-model:open="showDeleteDialog"
            title="Delete User"
            description="Are you sure you want to delete this user? This action cannot be undone."
            confirm-text="Delete"
            variant="destructive"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
