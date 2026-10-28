<?php

namespace Modules\Identity\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Identity\Contracts\Data\CustomerData;

/** Satu-satunya pintu baca data customer untuk modul lain. */
interface CustomerDirectory
{
    public function find(string $customerId): ?CustomerData;

    /** Customer yang sedang login di guard `customer` (null jika tamu). */
    public function current(): ?CustomerData;

    public function count(): int;

    /** @return LengthAwarePaginator<int, CustomerData> terbaru dulu; `search` mencocokkan nama/email/telepon/perusahaan */
    public function paginate(?string $search = null, int $perPage = 20): LengthAwarePaginator;

    /**
     * @param  list<string>  $ids
     * @return array<string, string> id => nama
     */
    public function names(array $ids): array;
}
