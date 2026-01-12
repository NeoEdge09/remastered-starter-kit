<script setup lang="ts">
import DynamicIcon from '@/components/DynamicIcon.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { icons } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const model = defineModel<string>({ default: '' });

const isOpen = ref(false);
const search = ref('');

// Convert PascalCase to kebab-case
const toKebabCase = (str: string): string => {
    return str
        .replace(/([a-z])([A-Z])/g, '$1-$2')
        .replace(/([A-Z])([A-Z][a-z])/g, '$1-$2')
        .toLowerCase();
};

// Get all icon names
const allIcons = computed(() => {
    return Object.keys(icons)
        .filter((name) => name !== 'default' && name !== 'icons')
        .map((name) => ({
            name: toKebabCase(name),
            pascalName: name,
        }))
        .sort((a, b) => a.name.localeCompare(b.name));
});

// Popular/common icons shown first
const popularIcons = [
    'home',
    'users',
    'user',
    'settings',
    'shield',
    'menu',
    'folder',
    'file',
    'file-text',
    'bell',
    'mail',
    'calendar',
    'chart-bar',
    'database',
    'lock',
    'key',
    'star',
    'heart',
    'flag',
    'bookmark',
    'search',
    'plus',
    'edit',
    'trash-2',
    'eye',
    'check',
    'x',
    'arrow-left',
    'arrow-right',
    'chevron-down',
    'chevron-up',
    'grid',
    'list',
    'layout-dashboard',
    'package',
    'box',
    'layers',
    'credit-card',
    'shopping-cart',
    'dollar-sign',
    'percent',
    'tag',
    'tags',
    'briefcase',
    'building',
    'globe',
    'map',
    'map-pin',
    'clock',
    'upload',
    'download',
    'image',
    'camera',
    'video',
    'music',
    'headphones',
    'phone',
    'message-circle',
    'message-square',
    'send',
    'paperclip',
    'link',
    'external-link',
    'share',
    'printer',
    'save',
    'refresh-cw',
    'rotate-cw',
    'filter',
    'sliders',
    'more-horizontal',
    'more-vertical',
    'code',
    'terminal',
    'git-branch',
    'activity',
    'bar-chart',
    'pie-chart',
    'trending-up',
    'trending-down',
    'alert-circle',
    'alert-triangle',
    'info',
    'help-circle',
    'zap',
    'power',
    'award',
    'trophy',
    'target',
    'clipboard',
    'copy',
    'scissors',
    'archive',
    'book',
    'book-open',
    'graduation-cap',
    'lightbulb',
];

// Filter icons based on search
const filteredIcons = computed(() => {
    const searchTerm = search.value.toLowerCase().trim();

    if (!searchTerm) {
        // Show popular icons first when no search
        const popular = allIcons.value.filter((icon) => popularIcons.includes(icon.name));
        const others = allIcons.value.filter((icon) => !popularIcons.includes(icon.name));
        return [...popular.slice(0, 48), ...others.slice(0, 100)];
    }

    return allIcons.value.filter((icon) => icon.name.includes(searchTerm)).slice(0, 150);
});

const selectIcon = (iconName: string) => {
    model.value = iconName;
    isOpen.value = false;
    search.value = '';
};

const clearIcon = () => {
    model.value = '';
};
</script>

<template>
    <div class="flex gap-2">
        <Popover v-model:open="isOpen">
            <PopoverTrigger as-child>
                <Button type="button" variant="outline" class="flex-1 justify-start gap-2 font-normal">
                    <template v-if="model">
                        <DynamicIcon :name="model" class="h-4 w-4" />
                        <span>{{ model }}</span>
                    </template>
                    <template v-else>
                        <span class="text-muted-foreground">Select an icon...</span>
                    </template>
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-80 p-0" align="start">
                <div class="border-b p-3">
                    <Input v-model="search" placeholder="Search icons..." class="h-8" autofocus />
                </div>
                <div class="max-h-72 overflow-y-auto p-2">
                    <div class="grid grid-cols-8 gap-1">
                        <button
                            v-for="icon in filteredIcons"
                            :key="icon.name"
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded hover:bg-accent"
                            :class="{ 'bg-primary text-primary-foreground hover:bg-primary/90': model === icon.name }"
                            :title="icon.name"
                            @click="selectIcon(icon.name)"
                        >
                            <DynamicIcon :name="icon.name" class="h-4 w-4" />
                        </button>
                    </div>
                    <p v-if="filteredIcons.length === 0" class="py-6 text-center text-sm text-muted-foreground">No icons found</p>
                    <p v-if="search && filteredIcons.length > 0" class="pt-2 text-center text-xs text-muted-foreground">
                        Showing {{ filteredIcons.length }} icons
                    </p>
                </div>
            </PopoverContent>
        </Popover>

        <!-- Preview / Clear -->
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md border">
            <DynamicIcon v-if="model" :name="model" class="h-4 w-4" />
            <span v-else class="text-xs text-muted-foreground">--</span>
        </div>

        <Button v-if="model" type="button" variant="ghost" size="icon" class="shrink-0" @click="clearIcon" title="Clear icon">
            <DynamicIcon name="x" class="h-4 w-4" />
        </Button>
    </div>
</template>
