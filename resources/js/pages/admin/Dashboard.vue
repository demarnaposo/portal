<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ClipboardCheck, UserCheck, Users } from '@lucide/vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDateTime } from '@/lib/format';
import { dashboard } from '@/routes/admin';
import { index as customersIndex, show } from '@/routes/admin/customers';
import type { CustomerRow } from '@/types';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Dashboard', href: dashboard() }] },
});

const props = defineProps<{
    stats: { customers: number; attempts: number; claimed_attempts: number };
    latestCustomers: CustomerRow[];
}>();

const tiles = [
    { label: 'Customer terdaftar', value: props.stats.customers, icon: Users },
    {
        label: 'Attempt assessment',
        value: props.stats.attempts,
        icon: ClipboardCheck,
    },
    {
        label: 'Hasil tersimpan ke akun',
        value: props.stats.claimed_attempts,
        icon: UserCheck,
    },
];
const number = new Intl.NumberFormat('id-ID');
</script>

<template>
    <Head title="Dashboard admin" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                Dashboard
            </h1>
            <p class="text-sm text-muted-foreground">
                Ringkasan portal peserta.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <Card v-for="tile in tiles" :key="tile.label" class="gap-2">
                <CardHeader class="flex flex-row items-center justify-between">
                    <CardTitle
                        class="text-sm font-medium text-muted-foreground"
                        >{{ tile.label }}</CardTitle
                    >
                    <component
                        :is="tile.icon"
                        class="size-4 text-brand-600"
                        aria-hidden="true"
                    />
                </CardHeader>
                <CardContent>
                    <p class="text-3xl font-bold text-brand-900 tabular-nums">
                        {{ number.format(tile.value) }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader class="flex flex-row items-center justify-between">
                <CardTitle class="text-base">Customer terbaru</CardTitle>
                <Link
                    :href="customersIndex()"
                    class="text-sm font-medium text-brand-700 hover:underline"
                    >Lihat semua</Link
                >
            </CardHeader>
            <CardContent>
                <p
                    v-if="!latestCustomers.length"
                    class="text-sm text-muted-foreground"
                >
                    Belum ada customer terdaftar.
                </p>
                <ul v-else class="divide-y divide-slate-100">
                    <li
                        v-for="c in latestCustomers"
                        :key="c.id"
                        class="flex items-center justify-between gap-4 py-3"
                    >
                        <div class="min-w-0">
                            <Link
                                :href="show(c.id)"
                                class="font-medium text-slate-900 hover:text-brand-700"
                                >{{ c.name }}</Link
                            >
                            <p class="truncate text-sm text-muted-foreground">
                                {{ c.email }}
                            </p>
                        </div>
                        <time
                            v-if="c.created_at"
                            :datetime="c.created_at"
                            class="shrink-0 text-xs text-muted-foreground"
                            >{{ formatDateTime(c.created_at) }}</time
                        >
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
