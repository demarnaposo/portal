<?php

use Modules\Admin\Domain\Admin;
use Modules\Identity\Domain\Customer;

return [

    // Customer (modul Identity) adalah pengguna default aplikasi; admin memakai guard terpisah.
    'defaults' => [
        'guard' => 'customer',
        'passwords' => 'customers',
    ],

    'guards' => [
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],
        // Area /admin (modul Admin, Fortify + 2FA wajib). Tabel `admins` terpisah dari `customers`.
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],
    ],

    'providers' => [
        'customers' => [
            'driver' => 'eloquent',
            'model' => Customer::class,
        ],
        'admins' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
        ],
    ],

    // Admin tidak punya reset sandi mandiri (dibuat/diatur lewat `php artisan admin:create`).
    'passwords' => [
        'customers' => [
            'provider' => 'customers',
            'table' => 'customer_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
