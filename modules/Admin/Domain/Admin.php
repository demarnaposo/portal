<?php

namespace Modules\Admin\Domain;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * Akun panel admin (guard `admin`, tabel `admins`). Bukan customer; 2FA TOTP wajib (EnsureTwoFactorEnabled).
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property AdminRole $role
 * @property ?Carbon $two_factor_confirmed_at
 */
class Admin extends Authenticatable
{
    use TwoFactorAuthenticatable;

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
}
