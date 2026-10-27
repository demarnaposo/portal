<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Pager from '@/components/admin/Pager.vue';
import ScoreChips from '@/components/admin/ScoreChips.vue';
import { Card, CardContent } from '@/components/ui/card';
import { formatDateTime } from '@/lib/format';
import { index } from '@/routes/admin/assessments';
import { show } from '@/routes/admin/customers';
import type { AssessmentResult, Paginated } from '@/types';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Hasil assessment', href: index() }] },
});

defineProps<{
    results: Paginated<AssessmentResult & { customer_name: string | null }>;
    anonymousCount: number;
}>();
</script>

<template>
    <Head title="Hasil assessment" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                Hasil assessment
            </h1>
            <p class="text-sm text-muted-foreground">
                Hasil yang sudah disimpan ke akun customer.
                {{ anonymousCount }} hasil anonim belum diklaim (dihapus
                otomatis setelah 30 hari).
            </p>
        </div>

        <Card class="py-0">
            <CardContent class="overflow-x-auto p-0">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b bg-slate-50 text-xs text-muted-foreground uppercase"
                    >
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Customer
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Rekomendasi
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Skor
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Waktu
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="!results.data.length">
                            <td
                                colspan="4"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                Belum ada hasil assessment tersimpan.
                            </td>
                        </tr>
                        <tr
                            v-for="r in results.data"
                            :key="r.id"
                            class="hover:bg-slate-50"
                        >
                            <td class="px-4 py-3">
                                <Link
                                    v-if="r.customer_id"
                                    :href="show(r.customer_id)"
                                    class="font-medium text-slate-900 hover:text-brand-700"
                                >
                                    {{ r.customer_name ?? 'Akun dihapus' }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {{ r.recommended_category.title }}
                            </td>
                            <td class="px-4 py-3">
                                <ScoreChips :result="r" />
                            </td>
                            <td
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{ formatDateTime(r.taken_at) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>

        <Pager :page="results" />
    </div>
</template>
