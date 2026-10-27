<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import SecurityController from '@/actions/Modules/Admin/Http/Controllers/Settings/SecurityController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import ManageTwoFactor from '@/components/ManageTwoFactor.vue';
import type { Props as ManageTwoFactorProps } from '@/components/ManageTwoFactor.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { edit } from '@/routes/admin/security';

// oxfmt-ignore
type Props = {
    passwordRules: string;
    status?: string | null;
} &
    ManageTwoFactorProps;

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Keamanan & 2FA', href: edit() }],
    },
});
</script>

<template>
    <Head title="Keamanan" />

    <Alert
        v-if="!twoFactorEnabled"
        class="border-accent-400 bg-accent-100 text-accent-700"
    >
        <AlertDescription class="text-accent-700">
            {{ status ?? 'Autentikasi dua faktor wajib.' }} Aktifkan 2FA di
            bawah untuk membuka panel admin.
        </AlertDescription>
    </Alert>

    <ManageTwoFactor
        :canManageTwoFactor="canManageTwoFactor"
        :requiresConfirmation="requiresConfirmation"
        :twoFactorEnabled="twoFactorEnabled"
    />

    <div class="space-y-6">
        <Heading
            variant="small"
            title="Ganti kata sandi"
            description="Minimal 12 karakter; gunakan kata sandi acak dari pengelola kata sandi."
        />

        <Form
            v-bind="SecurityController.update.form()"
            :options="{ preserveScroll: true }"
            reset-on-success
            :reset-on-error="[
                'password',
                'password_confirmation',
                'current_password',
            ]"
            class="space-y-6"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="current_password">Kata sandi saat ini</Label>
                <PasswordInput
                    id="current_password"
                    name="current_password"
                    autocomplete="current-password"
                />
                <InputError :message="errors.current_password" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Kata sandi baru</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    autocomplete="new-password"
                    :passwordrules="props.passwordRules"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation"
                    >Ulangi kata sandi baru</Label
                >
                <PasswordInput
                    id="password_confirmation"
                    name="password_confirmation"
                    autocomplete="new-password"
                    :passwordrules="props.passwordRules"
                />
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button :disabled="processing" data-test="update-password-button"
                >Simpan kata sandi</Button
            >
        </Form>
    </div>
</template>
