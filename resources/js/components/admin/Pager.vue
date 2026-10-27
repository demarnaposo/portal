<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types';

defineProps<{ page: Paginated<unknown> }>();
</script>

<template>
    <nav
        v-if="page.last_page > 1"
        class="flex items-center justify-between gap-4 text-sm text-muted-foreground"
        aria-label="Navigasi halaman"
    >
        <p>{{ page.from }}–{{ page.to }} dari {{ page.total }}</p>
        <div class="flex gap-2">
            <Button
                variant="outline"
                size="sm"
                :disabled="!page.prev_page_url"
                as-child
            >
                <Link
                    v-if="page.prev_page_url"
                    :href="page.prev_page_url"
                    preserve-scroll
                    >Sebelumnya</Link
                >
                <span v-else>Sebelumnya</span>
            </Button>
            <Button
                variant="outline"
                size="sm"
                :disabled="!page.next_page_url"
                as-child
            >
                <Link
                    v-if="page.next_page_url"
                    :href="page.next_page_url"
                    preserve-scroll
                    >Berikutnya</Link
                >
                <span v-else>Berikutnya</span>
            </Button>
        </div>
    </nav>
</template>
