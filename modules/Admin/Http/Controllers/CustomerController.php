<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Assessment\Contracts\AssessmentResults;
use Modules\Assessment\Contracts\Data\AssessmentResultData;
use Modules\Identity\Contracts\CustomerDirectory;
use Modules\Identity\Contracts\Data\CustomerData;

final class CustomerController
{
    public function index(Request $request, CustomerDirectory $customers): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/customers/Index', [
            'customers' => $customers->paginate($search === '' ? null : mb_substr($search, 0, 100))
                ->through(fn (CustomerData $c) => CustomerRow::from($c)),
            'filters' => ['search' => $search],
        ]);
    }

    public function show(string $customer, CustomerDirectory $customers, AssessmentResults $results): Response
    {
        $data = $customers->find($customer) ?? abort(404);

        return Inertia::render('admin/customers/Show', [
            'customer' => CustomerRow::from($data),
            'assessments' => array_map(fn (AssessmentResultData $r) => $r->toArray(), $results->forCustomer($data->id)),
        ]);
    }
}
