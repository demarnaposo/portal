<?php

namespace Modules\Admin\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Assessment\Contracts\AssessmentResults;
use Modules\Assessment\Contracts\Data\AssessmentResultData;
use Modules\Identity\Contracts\CustomerDirectory;

final class AssessmentResultController
{
    /** Hasil assessment yang sudah diklaim ke akun (attempt anonim hanya dihitung di dashboard). */
    public function __invoke(AssessmentResults $results, CustomerDirectory $customers): Response
    {
        $page = $results->paginateClaimed();
        $names = $customers->names(array_values(array_unique(array_map(fn (AssessmentResultData $r) => $r->customerId, $page->items()))));

        return Inertia::render('admin/assessments/Index', [
            'results' => $page->through(fn (AssessmentResultData $r) => [
                ...$r->toArray(),
                'customer_name' => $names[$r->customerId] ?? null,
            ]),
            'anonymousCount' => $results->count() - $results->count(claimedOnly: true),
        ]);
    }
}
