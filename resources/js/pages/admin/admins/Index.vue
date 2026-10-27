<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { index } from '@/routes/admin/admins';

defineOptions({
    layout: { breadcrumbs: [{ title: 'Admin', href: index() }] },
});

defineProps<{
    admins: {
        id: number;
        name: string;
        email: string;
        role: string;
        two_factor_enabled: boolean;
        created_at: string;
    }[];
}>();
</script>

<template>
    <Head title="Admin" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                Admin
            </h1>
            <p class="text-sm text-muted-foreground">
                Akun panel admin. Tambah admin lewat server:
                <code class="rounded bg-muted px-1"
                    >php artisan admin:create email@domain --role=operator</code
                >
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
                                Nama
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Email
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                Peran
                            </th>
                            <th scope="col" class="px-4 py-3 font-semibold">
                                2FA
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="a in admins" :key="a.id">
                            <td class="px-4 py-3 font-medium">{{ a.name }}</td>
                            <td class="px-4 py-3">{{ a.email }}</td>
                            <td class="px-4 py-3">{{ a.role }}</td>
                            <td class="px-4 py-3">
                                <Badge
                                    :variant="
                                        a.two_factor_enabled
                                            ? 'secondary'
                                            : 'destructive'
                                    "
                                    >{{
                                        a.two_factor_enabled
                                            ? 'Aktif'
                                            : 'Belum aktif'
                                    }}</Badge
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>
    </div>
</template>
