<?php

namespace Modules\Admin\Domain;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Modules\Admin\Notifications\ResetPasswordNotification;

/**
 * Akun panel admin (guard `admin`, tabel `admins`). Bukan customer; 2FA TOTP wajib (EnsureTwoFactorEnabled).
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property AdminRole $role
 * @property ?CarbonImmutable $two_factor_confirmed_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
class Admin extends Authenticatable
{
    use Notifiable, TwoFactorAuthenticatable;

    protected $fillable = ['name', 'email', 'password', 'role'];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => AdminRole::class,
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function isOwner(): bool
    {
        return $this->role === AdminRole::Owner;
    }

    /**
     * Dipanggil broker `admins` (reset sandi mandiri dari /admin/forgot-password).
     *
     * @param  string  $token
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
