<?php

namespace Tests\Feature\Admin;

use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Modules\Admin\Domain\Admin;
use Modules\Admin\Domain\AdminRole;

trait CreatesAdmins
{
    protected function admin(AdminRole $role = AdminRole::Owner, bool $twoFactor = true, array $attributes = []): Admin
    {
        $admin = Admin::create(array_merge([
            'name' => 'Admin Satu',
            'email' => 'admin'.uniqid().'@sertifika.test',
            'password' => 'sandi-admin-rahasia',
            'role' => $role,
        ], $attributes));

        if ($twoFactor) {
            $admin->forceFill([
                'two_factor_secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
                'two_factor_recovery_codes' => encrypt(json_encode(['kode-pemulihan-1', 'kode-pemulihan-2'])),
                'two_factor_confirmed_at' => now(),
            ])->save();
        }

        return $admin;
    }
}
