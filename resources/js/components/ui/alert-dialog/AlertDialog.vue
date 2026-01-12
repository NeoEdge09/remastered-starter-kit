<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogOverlay,
    DialogPortal,
    DialogRoot,
    DialogTitle,
    DialogTrigger,
} from 'radix-vue';

defineProps<{
    open?: boolean;
    title?: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
    cancel: [];
}>();

const handleConfirm = () => {
    emit('confirm');
    emit('update:open', false);
};

const handleCancel = () => {
    emit('cancel');
    emit('update:open', false);
};
</script>

<template>
    <DialogRoot :open="open" @update:open="emit('update:open', $event)">
        <DialogTrigger as-child>
            <slot name="trigger" />
        </DialogTrigger>
        <DialogPortal>
            <DialogOverlay
                class="fixed inset-0 z-50 bg-black/80 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0"
            />
            <DialogContent
                class="fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border bg-background p-6 shadow-lg duration-200 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%] sm:rounded-lg"
            >
                <div class="flex flex-col space-y-2 text-center sm:text-left">
                    <DialogTitle class="text-lg font-semibold">
                        {{ title || 'Are you sure?' }}
                    </DialogTitle>
                    <DialogDescription class="text-sm text-muted-foreground">
                        {{ description || 'This action cannot be undone.' }}
                    </DialogDescription>
                </div>
                <slot />
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
                    <DialogClose as-child>
                        <Button variant="outline" @click="handleCancel">
                            {{ cancelText || 'Cancel' }}
                        </Button>
                    </DialogClose>
                    <Button
                        :variant="variant || 'default'"
                        @click="handleConfirm"
                    >
                        {{ confirmText || 'Confirm' }}
                    </Button>
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
