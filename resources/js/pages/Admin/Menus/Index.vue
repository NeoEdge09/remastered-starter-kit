<script setup lang="ts">
import MenuTree from '@/components/MenuTree.vue';
import ToastNotification from '@/components/ToastNotification.vue';
import { AlertDialog } from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { usePermission } from '@/composables/usePermission';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Menu } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Info, Plus, RotateCcw, Save } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps<{
    page_setting: {
        title: string;
        subtitle: string;
        breadcrumbs: BreadcrumbItem[];
    };
    page_data: {
        menus: Menu[];
    };
}>();

const { can } = usePermission();

// Local copy of menus for drag-and-drop
const localMenus = ref<Menu[]>(JSON.parse(JSON.stringify(props.page_data.menus || [])));
const hasChanges = ref(false);
const isSaving = ref(false);

// Watch for prop changes (e.g., after save)
watch(
    () => props.page_data.menus,
    (newMenus) => {
        localMenus.value = JSON.parse(JSON.stringify(newMenus || []));
        hasChanges.value = false;
    },
    { deep: true },
);

const deleteMenu = ref<Menu | null>(null);
const showDeleteDialog = ref(false);

const handleEdit = (menu: Menu) => {
    router.visit(route('admin.menus.edit', { menu: menu.id }));
};

const handleDelete = (menu: Menu) => {
    deleteMenu.value = menu;
    showDeleteDialog.value = true;
};

const confirmDelete = () => {
    if (deleteMenu.value) {
        router.delete(route('admin.menus.destroy', { menu: deleteMenu.value.id }), {
            onSuccess: () => {
                showDeleteDialog.value = false;
                deleteMenu.value = null;
            },
        });
    }
};

const handleChange = () => {
    hasChanges.value = true;
};

// Flatten menus with order and parent_id for saving
interface MenuOrder {
    id: number;
    parent_id: number | null;
    order: number;
}

const flattenForSave = (menus: Menu[], parentId: number | null = null): MenuOrder[] => {
    const result: MenuOrder[] = [];
    menus.forEach((menu, index) => {
        result.push({
            id: menu.id,
            parent_id: parentId,
            order: index,
        });
        if (menu.children && menu.children.length > 0) {
            result.push(...flattenForSave(menu.children, menu.id));
        }
    });
    return result;
};

const saveOrder = () => {
    isSaving.value = true;
    const menuOrder = flattenForSave(localMenus.value);

    router.post(
        route('admin.menus.reorder'),
        { menus: menuOrder },
        {
            preserveScroll: true,
            onSuccess: () => {
                hasChanges.value = false;
            },
            onFinish: () => {
                isSaving.value = false;
            },
        },
    );
};

const resetOrder = () => {
    localMenus.value = JSON.parse(JSON.stringify(props.page_data.menus || []));
    hasChanges.value = false;
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
                        <div class="flex items-center gap-2">
                            <!-- Save/Reset buttons when changes exist -->
                            <template v-if="hasChanges">
                                <Button variant="outline" @click="resetOrder" :disabled="isSaving">
                                    <RotateCcw class="mr-2 h-4 w-4" />
                                    Reset
                                </Button>
                                <Button @click="saveOrder" :disabled="isSaving">
                                    <Save class="mr-2 h-4 w-4" />
                                    {{ isSaving ? 'Saving...' : 'Save Order' }}
                                </Button>
                            </template>
                            <Button v-if="can('menu.create')" as-child>
                                <Link :href="route('admin.menus.create')">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add Menu
                                </Link>
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Instructions -->
                    <div class="mb-6 flex items-start gap-3 rounded-lg border bg-muted/50 p-4">
                        <Info class="mt-0.5 h-5 w-5 shrink-0 text-muted-foreground" />
                        <div class="text-sm text-muted-foreground">
                            <p class="mb-1 font-medium text-foreground">Cara menggunakan:</p>
                            <ul class="list-inside list-disc space-y-1">
                                <li>Drag menu menggunakan ikon <strong>⋮⋮</strong> untuk mengubah urutan</li>
                                <li>Drop menu ke dalam menu lain untuk menjadikannya submenu</li>
                                <li>Klik <strong>Save Order</strong> setelah selesai mengurutkan</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Menu Tree -->
                    <div v-if="localMenus.length > 0" class="min-h-[200px]">
                        <MenuTree
                            v-model:menus="localMenus"
                            :can-edit="can('menu.edit')"
                            :can-delete="can('menu.delete')"
                            @edit="handleEdit"
                            @delete="handleDelete"
                            @change="handleChange"
                        />
                    </div>

                    <!-- Empty State -->
                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <div class="mb-4 rounded-full bg-muted p-4">
                            <Plus class="h-8 w-8 text-muted-foreground" />
                        </div>
                        <h3 class="mb-1 text-lg font-medium">No menus yet</h3>
                        <p class="mb-4 text-muted-foreground">Get started by creating your first menu item.</p>
                        <Button v-if="can('menu.create')" as-child>
                            <Link :href="route('admin.menus.create')">
                                <Plus class="mr-2 h-4 w-4" />
                                Add Menu
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Delete Confirmation Dialog -->
        <AlertDialog
            v-model:open="showDeleteDialog"
            title="Delete Menu"
            :description="
                deleteMenu?.children?.length
                    ? 'This menu has child items. Deleting it will also remove all child menus. Are you sure?'
                    : 'Are you sure you want to delete this menu?'
            "
            confirm-text="Delete"
            variant="destructive"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>
