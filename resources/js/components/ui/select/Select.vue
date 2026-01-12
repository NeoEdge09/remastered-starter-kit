<script setup lang="ts">
import { cn } from '@/lib/utils';
import {
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectItemIndicator,
    SelectItemText,
    SelectLabel,
    SelectPortal,
    SelectRoot,
    SelectScrollDownButton,
    SelectScrollUpButton,
    SelectSeparator,
    SelectTrigger,
    SelectValue,
    SelectViewport,
} from 'radix-vue';
import { Check, ChevronDown, ChevronUp } from 'lucide-vue-next';
import { computed, type HTMLAttributes } from 'vue';

// Special value to represent "no selection" since radix-vue doesn't allow empty strings
const NONE_VALUE = '__none__';

export interface SelectOption {
    value: string | number;
    label: string;
    disabled?: boolean;
}

export interface SelectGroupOption {
    label: string;
    options: SelectOption[];
}

const props = defineProps<{
    modelValue?: string | number | null;
    options?: SelectOption[];
    groups?: SelectGroupOption[];
    placeholder?: string;
    disabled?: boolean;
    class?: HTMLAttributes['class'];
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string | number | null];
}>();

// Convert empty string values to special NONE_VALUE for radix-vue compatibility
const normalizedOptions = computed(() => {
    return props.options?.map(option => ({
        ...option,
        value: option.value === '' ? NONE_VALUE : option.value,
    }));
});

const normalizedGroups = computed(() => {
    return props.groups?.map(group => ({
        ...group,
        options: group.options.map(option => ({
            ...option,
            value: option.value === '' ? NONE_VALUE : option.value,
        })),
    }));
});

// Convert modelValue for display (empty/null becomes NONE_VALUE)
const displayValue = computed(() => {
    if (props.modelValue === '' || props.modelValue === null || props.modelValue === undefined) {
        return NONE_VALUE;
    }
    return props.modelValue.toString();
});

// Handle value change - convert NONE_VALUE back to empty string/null
const handleValueChange = (value: string) => {
    if (value === NONE_VALUE) {
        emit('update:modelValue', null);
    } else {
        emit('update:modelValue', value);
    }
};
</script>

<template>
    <SelectRoot
        :model-value="displayValue"
        @update:model-value="handleValueChange"
        :disabled="disabled"
    >
        <SelectTrigger
            :class="cn(
                'flex h-9 w-full items-center justify-between whitespace-nowrap rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50 [&>span]:line-clamp-1',
                props.class,
            )"
        >
            <SelectValue :placeholder="placeholder" />
            <ChevronDown class="h-4 w-4 opacity-50" />
        </SelectTrigger>
        <SelectPortal>
            <SelectContent
                class="relative z-50 max-h-96 min-w-[8rem] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2"
                position="popper"
            >
                <SelectScrollUpButton class="flex cursor-default items-center justify-center py-1">
                    <ChevronUp class="h-4 w-4" />
                </SelectScrollUpButton>
                <SelectViewport class="p-1 h-[var(--radix-select-trigger-height)] w-full min-w-[var(--radix-select-trigger-width)]">
                    <!-- Simple options -->
                    <template v-if="normalizedOptions">
                        <SelectItem
                            v-for="option in normalizedOptions"
                            :key="option.value"
                            :value="option.value.toString()"
                            :disabled="option.disabled"
                            class="relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-2 pr-8 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                        >
                            <span class="absolute right-2 flex h-3.5 w-3.5 items-center justify-center">
                                <SelectItemIndicator>
                                    <Check class="h-4 w-4" />
                                </SelectItemIndicator>
                            </span>
                            <SelectItemText>{{ option.label }}</SelectItemText>
                        </SelectItem>
                    </template>
                    <!-- Grouped options -->
                    <template v-if="normalizedGroups">
                        <SelectGroup v-for="group in normalizedGroups" :key="group.label">
                            <SelectLabel class="px-2 py-1.5 text-sm font-semibold">
                                {{ group.label }}
                            </SelectLabel>
                            <SelectItem
                                v-for="option in group.options"
                                :key="option.value"
                                :value="option.value.toString()"
                                :disabled="option.disabled"
                                class="relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-2 pr-8 text-sm outline-none focus:bg-accent focus:text-accent-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50"
                            >
                                <span class="absolute right-2 flex h-3.5 w-3.5 items-center justify-center">
                                    <SelectItemIndicator>
                                        <Check class="h-4 w-4" />
                                    </SelectItemIndicator>
                                </span>
                                <SelectItemText>{{ option.label }}</SelectItemText>
                            </SelectItem>
                            <SelectSeparator class="-mx-1 my-1 h-px bg-muted" />
                        </SelectGroup>
                    </template>
                </SelectViewport>
                <SelectScrollDownButton class="flex cursor-default items-center justify-center py-1">
                    <ChevronDown class="h-4 w-4" />
                </SelectScrollDownButton>
            </SelectContent>
        </SelectPortal>
    </SelectRoot>
</template>
