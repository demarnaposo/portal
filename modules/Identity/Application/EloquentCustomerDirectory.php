<?php

namespace Modules\Identity\Application;

use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Identity\Contracts\CustomerDirectory;
use Modules\Identity\Contracts\Data\CustomerData;
use Modules\Identity\Domain\Customer;

final class EloquentCustomerDirectory implements CustomerDirectory
{
    public function __construct(private readonly AuthFactory $auth) {}

    public function find(string $customerId): ?CustomerData
    {
        return Customer::query()->find($customerId)?->toData();
    }

    public function current(): ?CustomerData
    {
        $user = $this->auth->guard('customer')->user();

        return $user instanceof Customer ? $user->toData() : null;
    }

    public function count(): int
    {
        return Customer::query()->count();
    }

    public function paginate(?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        $like = '%'.addcslashes((string) $search, '%_\\').'%';

        return Customer::query()
            ->when(filled($search), fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', $like)
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like)
                ->orWhere('company_name', 'like', $like)))
            ->latest()->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Customer $c) => $c->toData());
    }

    public function names(array $ids): array
    {
        return Customer::query()->whereIn('id', $ids)->pluck('name', 'id')->all();
    }
}
