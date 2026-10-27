<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <!-- Dibungkus: SidebarMenuButton mengecilkan svg anak langsung ([&>svg]:size-4). -->
    <div class="size-8 shrink-0"><AppLogoIcon class="size-8" light /></div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="truncate leading-tight font-semibold text-white"
            >Sertifika</span
        >
        <span class="truncate text-xs text-brand-200">Panel admin</span>
    </div>
</template>
