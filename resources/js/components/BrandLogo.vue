<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

// Logo dari CMS (site.seo.logo_url) bila ada, selain itu lambang bawaan — sama seperti landing.
defineProps<{ name: string; src?: string | null; light?: boolean }>();
</script>

<template>
    <span class="inline-flex items-center gap-2">
        <img
            v-if="src"
            :src="src"
            alt=""
            class="h-8 w-auto max-w-32 shrink-0 object-contain"
            decoding="async"
        />
        <AppLogoIcon v-else :light="light" class="h-8 w-8 shrink-0" />
        <span
            class="text-lg font-bold tracking-tight"
            :class="light ? 'text-white' : 'text-brand-900'"
            >{{ name }}</span
        >
    </span>
</template>
