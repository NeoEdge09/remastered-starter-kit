<script setup lang="ts">
import { Check, ChevronsUpDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { Button } from '../button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '../command';
import { Popover, PopoverContent, PopoverTrigger } from '../popover';
import { cn } from '@/lib/utils';

export interface ComboboxOption {
    value: string | number;
    label: string;
}

const props = withDefaults(
    defineProps<{
        modelValue?: string | number;
        options: ComboboxOption[];
        placeholder?: string;
        emptyText?: string;
        searchPlaceholder?: string;
        disabled?: boolean;
        class?: string;
    }>(),
    {
        placeholder: 'Select option...',
        emptyText: 'No results found.',
        searchPlaceholder: 'Search...',
        disabled: false,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void;
}>();

const open = ref(false);

const selectedOption = computed(() => {
    return props.options.find((option) => option.value == props.modelValue);
});

const selectOption = (value: string | number) => {
    emit('update:modelValue', value);
    open.value = false;
};
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                role="combobox"
                :aria-expanded="open"
                :disabled="disabled"
                :class="cn('w-full justify-between', !selectedOption && 'text-muted-foreground', props.class)"
            >
                {{ selectedOption?.label || placeholder }}
                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-full p-0" align="start">
            <Command>
                <CommandInput :placeholder="searchPlaceholder" />
                <CommandEmpty>{{ emptyText }}</CommandEmpty>
                <CommandList>
                    <CommandGroup>
                        <CommandItem
                            v-for="option in options"
                            :key="option.value"
                            :value="option.label"
                            @select="() => selectOption(option.value)"
                        >
                            <Check :class="cn('mr-2 h-4 w-4', modelValue == option.value ? 'opacity-100' : 'opacity-0')" />
                            {{ option.label }}
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
