<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Cms\Contracts\CmsApi;
use Modules\Cms\Contracts\CmsRequestFailed;

/** Placeholder sampai modul Commerce ada: ringkasan batch dari CMS (harga tidak pernah dari browser). */
final class CheckoutController
{
    public const MAX_QTY = 50;

    public function __invoke(Request $request, CmsApi $cms): Response
    {
        $batchId = $request->query('batch');
        $qty = max(1, min(self::MAX_QTY, (int) $request->query('qty', 1)));
        $detail = null;
        $unavailable = false;

        if (is_string($batchId) && preg_match('/^[\w\-]{1,100}$/', $batchId)) {
            try {
                $detail = $cms->batch($batchId);
            } catch (CmsRequestFailed $e) {
                Log::warning('Batch checkout gagal dimuat', ['error' => $e->getMessage()]);
                $unavailable = true;
            }
        }

        return Inertia::render('portal/Checkout', [
            'qty' => $qty,
            'unavailable' => $unavailable,
            'batch' => $detail ? [
                'id' => $detail['id'],
                'code' => $detail['code'],
                'title' => $detail['title'],
                'start_date' => $detail['start_date'],
                'end_date' => $detail['end_date'],
                'mode' => $detail['mode'],
                'location' => $detail['location'] ?? null,
                'status' => $detail['status'],
                'seats_left' => $detail['seats_left'],
                'price' => $detail['price'],
                'total' => $detail['price'] * $qty,
                'program' => ProgramCard::from($detail['program']),
            ] : null,
        ]);
    }
}
