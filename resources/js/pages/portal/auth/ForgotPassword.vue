<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import PortalAuthLayout from '@/components/portal/PortalAuthLayout.vue';
import TextField from '@/components/portal/TextField.vue';
import { login } from '@/routes/portal';
import { email } from '@/routes/portal/password';

const form = useForm({ email: '' });
const submit = () => form.post(email.url());
</script>

<template>
    <PortalAuthLayout
        title="Lupa kata sandi"
        subtitle="Masukkan email akun Anda. Kami akan mengirim tautan untuk mengatur ulang kata sandi."
    >
        <form class="card space-y-5" novalidate @submit.prevent="submit">
            <TextField
                v-model="form.email"
                label="Email"
                type="email"
                inputmode="email"
                autocomplete="email"
                required
                :error="form.errors.email"
            />
            <button
                type="submit"
                class="btn btn-primary w-full"
                :disabled="form.processing"
            >
                Kirim tautan
            </button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-600">
            <Link :href="login()" class="link">Kembali ke halaman masuk</Link>
        </p>
    </PortalAuthLayout>
</template>
