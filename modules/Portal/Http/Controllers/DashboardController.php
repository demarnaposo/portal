<?php

namespace Modules\Portal\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Assessment\Contracts\AssessmentResults;
use Modules\Cms\Contracts\CmsApi;
use Modules\Cms\Contracts\CmsRequestFailed;
use Modules\Identity\Contracts\CustomerDirectory;

final class DashboardController
{
    public const MAX_RECOMMENDATIONS = 3;

    public function __invoke(CustomerDirectory $customers, AssessmentResults $results, CmsApi $cms): Response
    {
        $latest = $results->forCustomer($customers->current()->id)[0] ?? null;
        $recommendations = [];
        $catalogUnavailable = false;

        if ($latest) {
            try {
                // Dihitung ulang dari katalog terkini (harga selalu dari CMS).
                $recommendations = array_map(
                    fn (array $p) => ProgramCard::from($p),
                    array_slice($cms->programs(['category' => $latest->recommendedCategorySlug]), 0, self::MAX_RECOMMENDATIONS),
                );
            } catch (CmsRequestFailed $e) {
                Log::warning('Rekomendasi dashboard gagal dimuat', ['error' => $e->getMessage()]);
                $catalogUnavailable = true;
            }
        }

        return Inertia::render('portal/Dashboard', [
            'latestAssessment' => $latest?->toArray(),
            'recommendations' => $recommendations,
            'catalogUnavailable' => $catalogUnavailable,
        ]);
    }
}
