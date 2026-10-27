<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import BrandLogo from '@/components/BrandLogo.vue';

// Meniru footer landing (astro/src/components/Footer.astro); kontak dari CMS /site.
const page = usePage();
const landing = page.props.landingUrl;
const year = new Date().getFullYear();
const whatsappUrl = (n: string) => `https://wa.me/${n.replace(/\D/g, '')}`;
</script>

<template>
    <footer class="bg-brand-950 text-slate-300">
        <div class="container-page grid gap-10 py-12 md:grid-cols-3">
            <div>
                <BrandLogo
                    :name="page.props.site.name"
                    :src="page.props.site.logo_url"
                    light
                />
                <p
                    v-if="page.props.site.tagline"
                    class="mt-3 max-w-xs text-sm text-slate-400"
                >
                    {{ page.props.site.tagline }}
                </p>
            </div>
            <nav aria-label="Tautan footer">
                <h2 class="text-sm font-semibold text-white">Jelajahi</h2>
                <ul class="mt-3 space-y-2 text-sm">
                    <li>
                        <a class="hover:text-white" :href="`${landing}/program`"
                            >Katalog program</a
                        >
                    </li>
                    <li>
                        <a
                            class="hover:text-white"
                            :href="`${landing}/assessment`"
                            >Assessment program</a
                        >
                    </li>
                    <li>
                        <a class="hover:text-white" :href="`${landing}/#faq`"
                            >Pertanyaan umum</a
                        >
                    </li>
                    <li>
                        <a
                            class="hover:text-white"
                            :href="`${landing}/kebijakan-privasi`"
                            >Kebijakan privasi</a
                        >
                    </li>
                </ul>
            </nav>
            <div v-if="page.props.site.contact">
                <h2 class="text-sm font-semibold text-white">Hubungi kami</h2>
                <address class="mt-3 space-y-2 text-sm not-italic">
                    <p>{{ page.props.site.contact.address }}</p>
                    <p>
                        <a
                            class="hover:text-white"
                            :href="`mailto:${page.props.site.contact.email}`"
                            >{{ page.props.site.contact.email }}</a
                        >
                    </p>
                    <p>
                        <a
                            class="hover:text-white"
                            :href="`tel:${page.props.site.contact.phone.replace(/[^\d+]/g, '')}`"
                            >{{ page.props.site.contact.phone }}</a
                        >
                    </p>
                    <p>
                        <a
                            class="hover:text-white"
                            :href="
                                whatsappUrl(page.props.site.contact.whatsapp)
                            "
                            rel="noopener"
                            target="_blank"
                        >
                            WhatsApp<span class="sr-only">
                                (membuka tab baru)</span
                            >
                        </a>
                    </p>
                </address>
            </div>
        </div>
        <div class="border-t border-white/10">
            <p class="container-page py-5 text-xs text-slate-400">
                &copy; {{ year }} {{ page.props.site.name }}. Seluruh harga
                dalam Rupiah dan sudah termasuk PPN.
            </p>
        </div>
    </footer>
</template>
