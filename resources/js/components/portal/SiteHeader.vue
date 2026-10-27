<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import BrandLogo from '@/components/BrandLogo.vue';
import { dashboard, login, logout, register } from '@/routes/portal';

// Meniru header landing (astro/src/components/Header.astro); tautan menu menuju landing.
const page = usePage();
const menuOpen = ref(false);
const landing = page.props.landingUrl;

const links = [
    { href: `${landing}/program`, label: 'Program' },
    { href: `${landing}/assessment`, label: 'Assessment' },
    { href: `${landing}/#faq`, label: 'FAQ' },
    { href: `${landing}/#kontak`, label: 'Kontak' },
];

const signOut = () => router.post(logout.url());
</script>

<template>
    <header
        class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur"
    >
        <div
            class="container-page flex h-16 items-center justify-between gap-4"
        >
            <a
                :href="landing"
                class="rounded-md"
                :aria-label="`${page.props.site.name} — beranda`"
            >
                <BrandLogo
                    :name="page.props.site.name"
                    :src="page.props.site.logo_url"
                />
            </a>

            <nav aria-label="Navigasi utama" class="hidden md:block">
                <ul class="flex items-center gap-1">
                    <li v-for="l in links" :key="l.href">
                        <a
                            :href="l.href"
                            class="rounded-md px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 hover:text-brand-800"
                            >{{ l.label }}</a
                        >
                    </li>
                </ul>
            </nav>

            <div class="flex items-center gap-2">
                <template v-if="page.props.auth.customer">
                    <span
                        class="hidden max-w-48 truncate text-sm text-slate-600 lg:inline"
                        >Halo, {{ page.props.auth.customer.name }}</span
                    >
                    <Link
                        :href="dashboard()"
                        class="btn btn-primary hidden px-4 py-2 sm:inline-flex"
                        >Dashboard</Link
                    >
                    <button
                        type="button"
                        class="btn btn-outline hidden px-4 py-2 sm:inline-flex"
                        @click="signOut"
                    >
                        Keluar
                    </button>
                </template>
                <template v-else>
                    <Link
                        :href="login()"
                        class="btn btn-outline hidden px-4 py-2 sm:inline-flex"
                        >Masuk</Link
                    >
                    <Link
                        :href="register()"
                        class="btn btn-primary hidden px-4 py-2 sm:inline-flex"
                        >Daftar</Link
                    >
                </template>
                <button
                    type="button"
                    class="inline-flex h-10 w-10 cursor-pointer items-center justify-center rounded-md text-slate-700 hover:bg-slate-100 md:hidden"
                    aria-controls="menu-mobile"
                    :aria-expanded="menuOpen"
                    @click="menuOpen = !menuOpen"
                >
                    <span class="sr-only">{{
                        menuOpen ? 'Tutup menu' : 'Buka menu'
                    }}</span>
                    <svg
                        aria-hidden="true"
                        viewBox="0 0 24 24"
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            v-if="menuOpen"
                            stroke-linecap="round"
                            d="M6 6l12 12M18 6L6 18"
                        />
                        <path
                            v-else
                            stroke-linecap="round"
                            d="M4 7h16M4 12h16M4 17h16"
                        />
                    </svg>
                </button>
            </div>
        </div>

        <nav
            v-show="menuOpen"
            id="menu-mobile"
            aria-label="Navigasi utama (seluler)"
            class="border-t border-slate-200 md:hidden"
        >
            <ul class="container-page flex flex-col py-2">
                <li v-for="l in links" :key="l.href">
                    <a
                        :href="l.href"
                        class="block rounded-md px-3 py-3 font-medium text-slate-700 hover:bg-slate-100"
                        >{{ l.label }}</a
                    >
                </li>
                <li
                    v-if="page.props.auth.customer"
                    class="grid gap-2 px-3 py-2"
                >
                    <Link :href="dashboard()" class="btn btn-primary w-full"
                        >Dashboard</Link
                    >
                    <button
                        type="button"
                        class="btn btn-outline w-full"
                        @click="signOut"
                    >
                        Keluar
                    </button>
                </li>
                <li v-else class="grid gap-2 px-3 py-2">
                    <Link :href="login()" class="btn btn-outline w-full"
                        >Masuk</Link
                    >
                    <Link :href="register()" class="btn btn-primary w-full"
                        >Daftar</Link
                    >
                </li>
            </ul>
        </nav>
    </header>
</template>
