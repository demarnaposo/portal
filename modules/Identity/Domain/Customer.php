<?php

namespace Modules\Identity\Domain;

use Carbon\CarbonImmutable;
use Illuminate\Auth\MustVerifyEmail as VerifiesEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Identity\Contracts\Data\CustomerData;
use Modules\Identity\Notifications\ResetPasswordNotification;
use Modules\Identity\Notifications\VerifyEmailNotification;

/**
 * Akun customer portal (guard `customer`). Terpisah dari admin (guard `admin`, tabel `admins`).
 *
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property ?string $company_name
 * @property ?string $npwp
 * @property ?string $billing_address
 * @property ?CarbonImmutable $email_verified_at
 * @property CarbonImmutable $privacy_accepted_at
 * @property ?CarbonImmutable $marketing_opt_in_at
 * @property ?CarbonImmutable $created_at
 * @property ?CarbonImmutable $updated_at
 */
class Customer extends Authenticatable implements MustVerifyEmail
{
    use HasUlids, Notifiable, VerifiesEmail;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'company_name', 'npwp', 'billing_address',
        'privacy_accepted_at', 'marketing_opt_in_at',
    ];

    protected $hidden = ['password', 'remember_token', 'npwp'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'privacy_accepted_at' => 'datetime',
            'marketing_opt_in_at' => 'datetime',
            'password' => 'hashed',
            'npwp' => 'encrypted',
        ];
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function toData(): CustomerData
    {
        return new CustomerData(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            phone: $this->phone,
            companyName: $this->company_name,
            emailVerified: $this->hasVerifiedEmail(),
            maskedNpwp: self::maskNpwp($this->npwp),
            createdAt: $this->created_at?->toIso8601String(),
        );
    }

    /** Hanya 4 digit terakhir yang terlihat, mis. ************1234. */
    public static function maskNpwp(?string $npwp): ?string
    {
        return $npwp === null || $npwp === '' ? null : str_repeat('*', max(0, strlen($npwp) - 4)).substr($npwp, -4);
    }
}
