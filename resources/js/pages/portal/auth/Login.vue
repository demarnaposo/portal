<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import PortalAuthLayout from '@/components/portal/PortalAuthLayout.vue';
import TextField from '@/components/portal/TextField.vue';
import { register } from '@/routes/portal';
import { store } from '@/routes/portal/login';
import { request } from '@/routes/portal/password';

const form = useForm({ email: '', password: '', remember: false });
const submit = () =>
    form.post(store.url(), { onFinish: () => form.reset('password') });
</script>

<template>
    <PortalAuthLayout
        title="Masuk ke portal"
        subtitle="Gunakan email dan kata sandi akun Anda."
    >
        <form class="card space-y-5" novalidate @submit.prevent="submit">
            <TextField
                v-model="form.email"
                label="Email"
                type="email"
                inputmode="email"
                autocomplete="username"
                required
                :error="form.errors.email"
            />
            <TextField
                v-model="form.password"
                label="Kata sandi"
                type="password"
                autocomplete="current-password"
                required
                :error="form.errors.password"
            />
            <div class="flex items-center justify-between gap-4 text-sm">
                <label class="inline-flex items-center gap-2 text-slate-700">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 accent-brand-700"
                    />
                    Ingat saya
                </label>
                <Link :href="request()" class="link">Lupa kata sandi?</Link>
            </div>
            <button
                type="submit"
                class="btn btn-primary w-full"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Memproses…' : 'Masuk' }}
            </button>
        </form>
        <p class="mt-6 text-center text-sm text-slate-600">
            Belum punya akun?
            <Link :href="register()" class="link">Daftar sekarang</Link>
        </p>
    </PortalAuthLayout>
</template>
