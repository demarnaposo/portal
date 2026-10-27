<?php

namespace Modules\Identity\Contracts\Data;

use Illuminate\Contracts\Support\Arrayable;

/** Data customer yang aman dibagikan ke modul lain (tanpa password; NPWP hanya versi tersamar). */
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
