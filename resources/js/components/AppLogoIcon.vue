<script setup lang="ts">
// Lambang yang sama dengan landing (astro/src/components/Logo.astro).
defineProps<{ light?: boolean }>();
</script>

<template>
    <svg aria-hidden="true" viewBox="0 0 32 32">
        <rect
            width="32"
            height="32"
            rx="8"
            :class="light ? 'fill-white/10' : 'fill-brand-700'"
        />
        <path
            d="M16 6l8 3.5v6c0 5-3.4 8.9-8 10.5-4.6-1.6-8-5.5-8-10.5v-6L16 6z"
            class="fill-accent-400"
        />
        <path
            d="M12.2 16.2l2.6 2.6 5-5.2"
            fill="none"
            stroke="#0d2647"
            stroke-width="2.2"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</template>
