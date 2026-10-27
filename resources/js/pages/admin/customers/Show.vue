<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import ScoreChips from '@/components/admin/ScoreChips.vue';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatDateTime } from '@/lib/format';
import { index, show } from '@/routes/admin/customers';
import type { AssessmentResult, CustomerRow } from '@/types';

const props = defineProps<{
    customer: CustomerRow;
    assessments: AssessmentResult[];
}>();

setLayoutProps({
    breadcrumbs: [
        { title: 'Customer', href: index() },
        { title: props.customer.name, href: show(props.customer.id) },
    ],
});
</script>

<template>
    <Head :title="props.customer.name" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <Link
                :href="index()"
                class="inline-flex items-center gap-1 text-sm text-brand-700 hover:underline"
            >
                <ArrowLeft class="size-4" aria-hidden="true" />Semua customer
            </Link>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                {{ customer.name }}
            </h1>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <Card class="lg:col-span-1">
                <CardHeader
                    ><CardTitle class="text-base"
                        >Data akun</CardTitle
                    ></CardHeader
                >
                <CardContent>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-muted-foreground">Email</dt>
                            <dd class="font-medium break-all">
                                {{ customer.email }}
                                <Badge
                                    class="ml-1"
                                    :variant="
                                        customer.email_verified
                                            ? 'secondary'
                                            : 'outline'
                                    "
                                    >{{
                                        customer.email_verified
                                            ? 'Terverifikasi'
                                            : 'Belum verifikasi'
                                    }}</Badge
                                >
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Telepon</dt>
                            <dd class="font-medium">{{ customer.phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Perusahaan</dt>
                            <dd class="font-medium">
                                {{ customer.company_name ?? '—' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">NPWP</dt>
                            <dd class="font-mono">
                                {{ customer.masked_npwp ?? '—' }}
                            </dd>
                            <dd
                                v-if="customer.masked_npwp"
                                class="text-xs text-muted-foreground"
                            >
                                Disamarkan; NPWP lengkap tersimpan terenkripsi.
                            </dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">Terdaftar</dt>
                            <dd class="font-medium">
                                {{
                                    customer.created_at
                                        ? formatDateTime(customer.created_at)
                                        : '—'
                                }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <Card class="lg:col-span-2">
                <CardHeader
                    ><CardTitle class="text-base"
                        >Hasil assessment</CardTitle
                    ></CardHeader
                >
                <CardContent>
                    <p
                        v-if="!assessments.length"
                        class="text-sm text-muted-foreground"
                    >
                        Belum ada hasil assessment tersimpan.
                    </p>
                    <ol v-else class="divide-y divide-slate-100">
                        <li
                            v-for="r in assessments"
                            :key="r.id"
                            class="space-y-2 py-3"
                        >
                            <div
                                class="flex flex-wrap items-baseline justify-between gap-2"
                            >
                                <p class="font-medium text-slate-900">
                                    Rekomendasi:
                                    {{ r.recommended_category.title }}
                                </p>
                                <time
                                    :datetime="r.taken_at"
                                    class="text-xs text-muted-foreground"
                                    >{{ formatDateTime(r.taken_at) }}</time
                                >
                            </div>
                            <ScoreChips :result="r" />
                        </li>
                    </ol>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
