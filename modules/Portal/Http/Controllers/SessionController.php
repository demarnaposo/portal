<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Identity\Contracts\CustomerDirectory;

final class SessionController
{
    /** GET /api/session → { authenticated, name? } — sengaja tanpa data lain (dibaca lintas origin oleh landing). */
    public function __invoke(CustomerDirectory $customers): JsonResponse
    {
        $customer = $customers->current();

        return response()
            ->json($customer ? ['authenticated' => true, 'name' => $customer->name] : ['authenticated' => false])
            ->header('Cache-Control', 'no-store, private');
    }
}
