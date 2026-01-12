<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-vue-next';
import type { PaginatedData } from '@/types';

defineProps<{
    data: PaginatedData<unknown>;
}>();
</script>

<template>
    <div class="flex items-center justify-between px-2 py-4">
        <div class="text-sm text-muted-foreground">
            Showing {{ data.meta.from || 0 }} to {{ data.meta.to || 0 }} of {{ data.meta.total }} results
        </div>
        <div class="flex items-center gap-1" v-if="data.meta.last_page > 1">
            <!-- First Page -->
            <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                :disabled="!data.links.prev"
                as-child
            >
                <Link v-if="data.links.prev" :href="data.links.first" preserve-scroll>
                    <ChevronsLeft class="h-4 w-4" />
                </Link>
                <span v-else><ChevronsLeft class="h-4 w-4" /></span>
            </Button>

            <!-- Previous Page -->
            <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                :disabled="!data.links.prev"
                as-child
            >
                <Link v-if="data.links.prev" :href="data.links.prev" preserve-scroll>
                    <ChevronLeft class="h-4 w-4" />
                </Link>
                <span v-else><ChevronLeft class="h-4 w-4" /></span>
            </Button>

            <!-- Page Numbers -->
            <template v-for="(link, index) in data.meta.links" :key="index">
                <!-- Skip Previous/Next links from the array to avoid duplication -->
                <template v-if="!link.label.includes('Previous') && !link.label.includes('Next')">
                    <span v-if="link.url === null" class="px-2 text-muted-foreground">...</span>
                    <Button
                        v-else
                        :variant="link.active ? 'default' : 'outline'"
                        size="icon"
                        class="h-8 w-8"
                        as-child
                    >
                        <Link :href="link.url" preserve-scroll>
                            {{ link.label }}
                        </Link>
                    </Button>
                </template>
            </template>

            <!-- Next Page -->
            <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                :disabled="!data.links.next"
                as-child
            >
                <Link v-if="data.links.next" :href="data.links.next" preserve-scroll>
                    <ChevronRight class="h-4 w-4" />
                </Link>
                <span v-else><ChevronRight class="h-4 w-4" /></span>
            </Button>

            <!-- Last Page -->
            <Button
                variant="outline"
                size="icon"
                class="h-8 w-8"
                :disabled="!data.links.next"
                as-child
            >
                <Link v-if="data.links.next" :href="data.links.last" preserve-scroll>
                    <ChevronsRight class="h-4 w-4" />
                </Link>
                <span v-else><ChevronsRight class="h-4 w-4" /></span>
            </Button>
        </div>
    </div>
</template>
