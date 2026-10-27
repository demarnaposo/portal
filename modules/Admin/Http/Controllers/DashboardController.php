<?php

namespace Modules\Admin\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Assessment\Contracts\AssessmentResults;
use Modules\Identity\Contracts\CustomerDirectory;
use Modules\Identity\Contracts\Data\CustomerData;

final class DashboardController
{
    public function __invoke(CustomerDirectory $customers, AssessmentResults $results): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => [
                'customers' => $customers->count(),
                'attempts' => $results->count(),
                'claimed_attempts' => $results->count(claimedOnly: true),
            ],
            'latestCustomers' => array_map(
                fn (CustomerData $c) => CustomerRow::from($c),
                $customers->paginate(perPage: 5)->items(),
            ),
        ]);
    }
}
