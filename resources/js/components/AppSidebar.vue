<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    ClipboardCheck,
    LayoutGrid,
    RefreshCw,
    ShieldCheck,
    ShoppingCart,
    Ticket,
    UserRound,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes/admin';
import { index as admins } from '@/routes/admin/admins';
import { index as assessments } from '@/routes/admin/assessments';
import { index as customers } from '@/routes/admin/customers';
import type { NavItem } from '@/types';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => [
    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
    { title: 'Customer', href: customers(), icon: Users },
    { title: 'Hasil assessment', href: assessments(), icon: ClipboardCheck },
    ...(page.props.auth.user?.role === 'owner'
        ? [{ title: 'Admin', href: admins(), icon: ShieldCheck }]
        : []),
]);

// Modul berikutnya (Commerce, Learner, integrasi Odoo): tampil di menu, belum bisa dibuka.
const upcomingNavItems = [
    { title: 'Pesanan', icon: ShoppingCart },
    { title: 'Peserta', icon: UserRound },
    { title: 'Kuota batch', icon: Ticket },
    { title: 'Sinkron Odoo', icon: RefreshCw },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain label="Operasional" :items="mainNavItems" />
            <NavMain label="Segera hadir" :upcoming="upcomingNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
