<script setup lang="ts">
import DynamicIcon from '@/components/DynamicIcon.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Menu } from '@/types';
import { ChevronDown, ChevronRight, Edit, GripVertical, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import draggable from 'vuedraggable';

const props = defineProps<{
    menus: Menu[];
    canEdit?: boolean;
    canDelete?: boolean;
}>();

const emit = defineEmits<{
    'update:menus': [menus: Menu[]];
    edit: [menu: Menu];
    delete: [menu: Menu];
    change: [];
}>();

// Track collapsed state
const collapsed = ref<Record<number, boolean>>({});

const toggleCollapse = (menuId: number) => {
    collapsed.value[menuId] = !collapsed.value[menuId];
};

const isCollapsed = (menuId: number) => {
    return collapsed.value[menuId] ?? false;
};

const handleChange = () => {
    emit('change');
};

const updateChildren = (menu: Menu, newChildren: Menu[]) => {
    menu.children = newChildren;
    handleChange();
};
</script>

<template>
    <draggable
        :model-value="menus"
        @update:model-value="$emit('update:menus', $event)"
        group="menus"
        item-key="id"
        handle=".drag-handle"
        ghost-class="menu-ghost"
        chosen-class="menu-chosen"
        drag-class="menu-drag"
        animation="200"
        @change="handleChange"
        class="menu-tree space-y-2"
    >
        <template #item="{ element: menu }">
            <div class="menu-item">
                <!-- Menu Card -->
                <div class="flex items-center gap-2 rounded-lg border bg-card p-3 shadow-sm transition-colors hover:bg-accent/50">
                    <!-- Drag Handle -->
                    <div class="drag-handle cursor-grab text-muted-foreground hover:text-foreground active:cursor-grabbing">
                        <GripVertical class="h-5 w-5" />
                    </div>

                    <!-- Collapse Toggle (if has children) -->
                    <button v-if="menu.children && menu.children.length > 0" @click="toggleCollapse(menu.id)" class="rounded p-1 hover:bg-muted">
                        <ChevronRight v-if="isCollapsed(menu.id)" class="h-4 w-4" />
                        <ChevronDown v-else class="h-4 w-4" />
                    </button>
                    <div v-else class="w-6" />

                    <!-- Icon -->
                    <div class="flex h-8 w-8 items-center justify-center rounded bg-muted">
                        <DynamicIcon v-if="menu.icon" :name="menu.icon" class="h-4 w-4" />
                        <span v-else class="text-xs text-muted-foreground">—</span>
                    </div>

                    <!-- Menu Info -->
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="truncate font-medium">{{ menu.name }}</span>
                            <Badge v-if="!menu.is_active" variant="secondary" class="text-xs"> Inactive </Badge>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-muted-foreground">
                            <span v-if="menu.route_name" class="truncate">{{ menu.route_name }}</span>
                            <span v-else-if="menu.url" class="truncate">{{ menu.url }}</span>
                            <span v-else>No link</span>
                            <span v-if="menu.permission_name" class="text-primary"> · {{ menu.permission_name }} </span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-1">
                        <Button v-if="canEdit" variant="ghost" size="icon" class="h-8 w-8" @click="$emit('edit', menu)">
                            <Edit class="h-4 w-4" />
                        </Button>
                        <Button
                            v-if="canDelete"
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8 text-destructive hover:text-destructive"
                            @click="$emit('delete', menu)"
                        >
                            <Trash2 class="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                <!-- Nested Children -->
                <div
                    v-if="menu.children && menu.children.length > 0"
                    v-show="!isCollapsed(menu.id)"
                    class="ml-8 mt-2 border-l-2 border-dashed border-muted pl-4"
                >
                    <MenuTree
                        :menus="menu.children"
                        :can-edit="canEdit"
                        :can-delete="canDelete"
                        @update:menus="updateChildren(menu, $event)"
                        @edit="$emit('edit', $event)"
                        @delete="$emit('delete', $event)"
                        @change="handleChange"
                    />
                </div>

                <!-- Drop Zone for new children (when empty) -->
                <draggable
                    v-if="!menu.children || menu.children.length === 0"
                    :model-value="menu.children || []"
                    @update:model-value="updateChildren(menu, $event)"
                    group="menus"
                    item-key="id"
                    ghost-class="menu-ghost"
                    animation="200"
                    @change="handleChange"
                    class="ml-8 mt-2 min-h-[40px] rounded-lg border-2 border-dashed border-transparent transition-colors hover:border-muted"
                >
                    <template #item="{ element }">
                        <div>{{ element.name }}</div>
                    </template>
                </draggable>
            </div>
        </template>
    </draggable>
</template>

<style scoped>
.menu-ghost {
    opacity: 0.5;
    background: hsl(var(--primary) / 0.1);
    border-radius: 0.5rem;
}

.menu-chosen {
    opacity: 0.8;
}

.menu-drag {
    opacity: 1;
}

.menu-tree {
    min-height: 50px;
}
</style>
