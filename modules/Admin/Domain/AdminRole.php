<?php

namespace Modules\Admin\Domain;

enum AdminRole: string
{
    /** Akses penuh, termasuk daftar admin. */
    case Owner = 'owner';

    /** Operasional harian (customer, hasil assessment; nanti pesanan & peserta). */
    case Operator = 'operator';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Pemilik',
            self::Operator => 'Operator',
        };
    }
}
