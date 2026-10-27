import { createInertiaApp } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

void createInertiaApp({
    title: (title) => (title ? `${title} · Sertifika` : 'Sertifika'),
    // Halaman portal customer membungkus dirinya sendiri dengan PortalLayout/PortalAuthLayout.
    layout: (name) => {
        switch (true) {
            case name.startsWith('portal/'):
                return null;
            case name.startsWith('admin/auth/'):
                return AuthLayout;
            case name.startsWith('admin/settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#f2b84b',
    },
});

// Toast dari Inertia::flash('toast', …) (panel admin).
initializeFlashToast();
