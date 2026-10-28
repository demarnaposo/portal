<?php

namespace Modules\Identity\Contracts\Data;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Data customer yang aman dibagikan ke modul lain (tanpa password; NPWP hanya versi tersamar).
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class CustomerData implements Arrayable
{
    public function __construct(
        public string $id,
        public string $name,
        public string $email,
        public string $phone,
        public ?string $companyName,
        public bool $emailVerified,
        public ?string $maskedNpwp = null,
        public ?string $createdAt = null,
    ) {}

    /** @return array{id: string, name: string, email: string, phone: string, company_name: ?string, email_verified: bool, masked_npwp: ?string, created_at: ?string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->companyName,
            'email_verified' => $this->emailVerified,
            'masked_npwp' => $this->maskedNpwp,
            'created_at' => $this->createdAt,
        ];
    }
}
