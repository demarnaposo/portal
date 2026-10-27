<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { LucideIcon } from '@lucide/vue';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuBadge,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import type { NavItem } from '@/types';

defineProps<{
    label: string;
    items?: NavItem[];
    /** Menu modul yang belum tersedia: tampil nonaktif dengan label "Segera". */
    upcoming?: { title: string; icon: LucideIcon }[];
}>();

const { isCurrentOrParentUrl, isCurrentUrl } = useCurrentUrl();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel class="text-brand-300">{{
            label
        }}</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton
                    as-child
                    :is-active="
                        item.title === 'Dashboard'
                            ? isCurrentUrl(item.href)
                            : isCurrentOrParentUrl(item.href)
                    "
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
            <SidebarMenuItem v-for="item in upcoming" :key="item.title">
                <SidebarMenuButton
                    disabled
                    aria-disabled="true"
                    :tooltip="`${item.title} (segera hadir)`"
                    class="cursor-not-allowed opacity-60"
                >
                    <component :is="item.icon" />
                    <span>{{ item.title }}</span>
                </SidebarMenuButton>
                <SidebarMenuBadge
                    class="rounded-full bg-accent-400/15 text-[10px] text-accent-400"
                    >Segera</SidebarMenuBadge
                >
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
