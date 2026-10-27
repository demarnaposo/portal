<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import PortalAuthLayout from '@/components/portal/PortalAuthLayout.vue';
import TextField from '@/components/portal/TextField.vue';
import { store } from '@/routes/portal/password';

const props = defineProps<{ token: string; email: string }>();
const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});
const submit = () =>
    form.post(store.url(), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
</script>

<template>
    <PortalAuthLayout title="Atur ulang kata sandi">
        <form class="card space-y-5" novalidate @submit.prevent="submit">
            <TextField
                v-model="form.email"
                label="Email"
                type="email"
                autocomplete="username"
                required
                :error="form.errors.email"
            />
            <TextField
                v-model="form.password"
                label="Kata sandi baru"
                type="password"
                autocomplete="new-password"
                required
                :error="form.errors.password"
                hint="Minimal 8 karakter."
            />
            <TextField
                v-model="form.password_confirmation"
                label="Ulangi kata sandi baru"
                type="password"
                autocomplete="new-password"
                required
            />
            <button
                type="submit"
                class="btn btn-primary w-full"
                :disabled="form.processing"
            >
                Simpan kata sandi
            </button>
        </form>
    </PortalAuthLayout>
</template>
