<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import PortalAuthLayout from '@/components/portal/PortalAuthLayout.vue';
import { logout } from '@/routes/portal';
import { send } from '@/routes/portal/verification';

defineProps<{ email: string }>();
const form = useForm({});
const resend = () => form.post(send.url());
const signOut = () => router.post(logout.url());
</script>

<template>
    <PortalAuthLayout title="Verifikasi email Anda">
        <div class="card space-y-4 text-sm text-slate-700">
            <p>
                Kami telah mengirim tautan verifikasi ke
                <strong class="text-slate-900">{{ email }}</strong
                >. Buka email tersebut dan klik tautannya untuk mengaktifkan
                akun.
            </p>
            <p class="text-slate-500">
                Tidak menerima email? Periksa folder spam, atau kirim ulang.
            </p>
            <div class="flex flex-col gap-3 sm:flex-row">
                <button
                    type="button"
                    class="btn btn-primary"
                    :disabled="form.processing"
                    @click="resend"
                >
                    Kirim ulang email verifikasi
                </button>
                <button type="button" class="btn btn-outline" @click="signOut">
                    Keluar
                </button>
            </div>
        </div>
    </PortalAuthLayout>
</template>
