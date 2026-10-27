<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import FlashMessage from '@/components/portal/FlashMessage.vue';
import SiteFooter from '@/components/portal/SiteFooter.vue';
import SiteHeader from '@/components/portal/SiteHeader.vue';

defineProps<{ title: string; subtitle?: string }>();
const page = usePage();

const benefits = [
    'Simpan hasil assessment & rekomendasi program',
    'Pantau pesanan dan jadwal batch',
    'Unduh dokumen & sertifikat',
];
</script>

<template>
    <Head :title="title" />
    <div class="flex min-h-screen flex-col">
        <SiteHeader />
        <main id="konten" class="flex-1 bg-slate-50">
            <div
                class="container-page grid gap-10 py-10 lg:grid-cols-2 lg:items-start lg:py-16"
            >
                <section
                    class="hidden lg:block"
                    aria-label="Keuntungan akun peserta"
                >
                    <p class="eyebrow">Portal Peserta</p>
                    <p
                        class="mt-3 max-w-md text-3xl leading-tight font-bold tracking-tight text-slate-900"
                    >
                        Kelola pelatihan dan sertifikasi Anda di satu tempat.
                    </p>
                    <ul class="mt-8 space-y-3 text-slate-700">
                        <li v-for="b in benefits" :key="b" class="flex gap-3">
                            <svg
                                aria-hidden="true"
                                viewBox="0 0 20 20"
                                class="mt-0.5 h-5 w-5 shrink-0 fill-accent-500"
                            >
                                <path
                                    d="M8.1 13.3 4.8 10l-1.1 1.1 4.4 4.4 9.2-9.2-1.1-1.1z"
                                />
                            </svg>
                            {{ b }}
                        </li>
                    </ul>
                </section>

                <div class="mx-auto w-full max-w-md">
                    <h1
                        class="text-2xl font-bold tracking-tight text-slate-900"
                    >
                        {{ title }}
                    </h1>
                    <p v-if="subtitle" class="mt-2 text-sm text-slate-600">
                        {{ subtitle }}
                    </p>
                    <div class="mt-6 space-y-4 empty:hidden">
                        <FlashMessage :message="page.props.flash.status" />
                        <FlashMessage
                            v-if="page.props.assessment?.claimed"
                            message="Hasil assessment Anda telah disimpan ke akun."
                        />
                        <FlashMessage
                            v-if="page.props.assessment?.pending"
                            tone="info"
                            message="Hasil assessment Anda akan otomatis tersimpan ke akun setelah Anda masuk atau mendaftar."
                        />
                    </div>
                    <div class="mt-6">
                        <slot />
                    </div>
                </div>
            </div>
        </main>
        <SiteFooter />
    </div>
</template>
