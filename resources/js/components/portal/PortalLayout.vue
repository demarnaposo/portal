<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import FlashMessage from '@/components/portal/FlashMessage.vue';
import SiteFooter from '@/components/portal/SiteFooter.vue';
import SiteHeader from '@/components/portal/SiteHeader.vue';
import { assessments, dashboard, orders, programs } from '@/routes/portal';
import { edit as profile } from '@/routes/portal/profile';

defineProps<{
    title: string;
    active: 'dashboard' | 'assessments' | 'orders' | 'programs' | 'profile';
}>();
const page = usePage();

const nav = [
    { key: 'dashboard', label: 'Dashboard', href: dashboard() },
    { key: 'assessments', label: 'Assessment saya', href: assessments() },
    { key: 'orders', label: 'Pesanan', href: orders() },
    { key: 'programs', label: 'Program saya', href: programs() },
    { key: 'profile', label: 'Profil', href: profile() },
];
</script>

<template>
    <Head :title="title" />
    <div class="flex min-h-screen flex-col">
        <a
            href="#konten"
            class="sr-only z-50 rounded-md bg-brand-700 px-4 py-2 text-white focus:not-sr-only focus:fixed focus:top-3 focus:left-3"
        >
            Lewati ke konten utama
        </a>
        <SiteHeader />

        <nav
            aria-label="Menu portal"
            class="border-b border-slate-200 bg-slate-50"
        >
            <ul class="container-page flex gap-1 overflow-x-auto py-2">
                <li v-for="item in nav" :key="item.key" class="shrink-0">
                    <Link
                        :href="item.href"
                        class="block rounded-md px-3 py-2 text-sm font-medium transition-colors"
                        :class="
                            item.key === active
                                ? 'bg-white text-brand-800 shadow-sm ring-1 ring-slate-200'
                                : 'text-slate-600 hover:bg-white hover:text-brand-800'
                        "
                        :aria-current="item.key === active ? 'page' : undefined"
                    >
                        {{ item.label }}
                    </Link>
                </li>
            </ul>
        </nav>

        <main
            id="konten"
            class="container-page flex-1 py-8 sm:py-10"
            tabindex="-1"
        >
            <div class="mb-6 space-y-3 empty:hidden">
                <FlashMessage :message="page.props.flash.status" />
                <FlashMessage
                    v-if="page.props.assessment?.claimed"
                    message="Hasil assessment Anda telah disimpan ke akun."
                />
            </div>
            <slot />
        </main>

        <SiteFooter />
    </div>
</template>
