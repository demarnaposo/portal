<?php

namespace Modules\Admin\Http\Controllers;

use Modules\Identity\Contracts\Data\CustomerData;

/** Baris customer untuk panel admin: NPWP hanya versi tersamar. */
final class CustomerRow
{
    /** @return array{id: string, name: string, email: string, phone: string, company_name: ?string, masked_npwp: ?string, email_verified: bool, created_at: ?string} */
    public static function from(CustomerData $c): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'email' => $c->email,
            'phone' => $c->phone,
            'company_name' => $c->companyName,
            'masked_npwp' => $c->maskedNpwp,
            'email_verified' => $c->emailVerified,
            'created_at' => $c->createdAt,
        ];
    }
}
