<script setup lang="ts">
import BrandLogo from '@/components/BrandLogo.vue';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh flex-col items-center justify-center gap-6 bg-brand-950 p-6 md:p-10"
    >
        <div class="w-full max-w-sm">
            <div class="mb-6 flex justify-center">
                <BrandLogo name="Sertifika" light />
            </div>
            <main class="flex flex-col gap-8 rounded-xl bg-white p-8 shadow-xl">
                <div class="space-y-2 text-center">
                    <h1 class="text-xl font-bold text-slate-900">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </main>
        </div>
    </div>
</template>
