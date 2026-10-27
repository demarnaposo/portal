<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search } from '@lucide/vue';
import { ref } from 'vue';
import Pager from '@/components/admin/Pager.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { formatDateTime } from '@/lib/format';
import { index, show } from '@/routes/admin/customers';
import type { CustomerRow, Paginated } from '@/types';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Customer', href: index() }] },
});

const props = defineProps<{
    customers: Paginated<CustomerRow>;
    filters: { search: string };
}>();

const search = ref(props.filters.search);
const submit = () =>
    router.get(
        index.url({ query: search.value ? { search: search.value } : {} }),
        {},
        { preserveState: true },
    );
</script>

<template>
    <Head title="Customer" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
        >
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Customer
                </h1>
                <p class="text-sm text-muted-foreground">
                    {{ customers.total }} akun peserta terdaftar.
                </p>
            </div>
            <form
                class="flex w-full gap-2 sm:w-auto"
                role="search"
                @submit.prevent="submit"
            >
                <label for="search" class="sr-only">Cari customer</label>
                <Input
                    id="search"
                    v-model="search"
                    type="search"
                    placeholder="Nama, email, telepon, perusahaan"
                    class="sm:w-72"
                />
                <Button type="submit" variant="secondary"
                    ><Search aria-hidden="true" />Cari</Button
                >
            </form>
        </div>

        <Card class="py-0">
            <CardContent class="overflow-x-auto p-0">
                <table class="w-full text-left text-sm">
                    <thead
                        class="border-b bg-slate-50 text-xs text-muted-foreground uppercase"
                    >
                        <tr>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Nama
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Kontak
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Perusahaan
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Terdaftar
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-if="!customers.data.length">
                            <td
                                colspan="5"
                                class="px-4 py-10 text-center text-muted-foreground"
                            >
                                {{
                                    filters.search
                                        ? 'Tidak ada customer yang cocok.'
                                        : 'Belum ada customer terdaftar.'
                                }}
                            </td>
                        </tr>
                        <tr
                            v-for="c in customers.data"
                            :key="c.id"
                            class="hover:bg-slate-50"
                        >
                            <td class="px-4 py-3">
                                <Link
                                    :href="show(c.id)"
                                    class="font-medium text-slate-900 hover:text-brand-700"
                                    >{{ c.name }}</Link
                                >
                            </td>
                            <td class="px-4 py-3">
                                <p>{{ c.email }}</p>
                                <p class="text-muted-foreground">
                                    {{ c.phone }}
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                <p>{{ c.company_name ?? '—' }}</p>
                                <p
                                    v-if="c.masked_npwp"
                                    class="font-mono text-xs text-muted-foreground"
                                >
                                    NPWP {{ c.masked_npwp }}
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="
                                        c.email_verified
                                            ? 'secondary'
                                            : 'outline'
                                    "
                                >
                                    {{
                                        c.email_verified
                                            ? 'Terverifikasi'
                                            : 'Belum verifikasi'
                                    }}
                                </Badge>
                            </td>
                            <td
                                class="px-4 py-3 whitespace-nowrap text-muted-foreground"
                            >
                                {{
                                    c.created_at
                                        ? formatDateTime(c.created_at)
                                        : '—'
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>

        <Pager :page="customers" />
    </div>
</template>
