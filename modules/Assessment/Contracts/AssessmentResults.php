<?php

namespace Modules\Assessment\Contracts;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Assessment\Contracts\Data\AssessmentResultData;

/** Hasil assessment yang tersimpan (anonim atau sudah diklaim ke akun customer). */
interface AssessmentResults
{
    /** @return list<AssessmentResultData> terbaru dulu */
    public function forCustomer(string $customerId): array;

    /** Semua attempt, atau hanya yang sudah diklaim ke akun. */
    public function count(bool $claimedOnly = false): int;

    /** @return LengthAwarePaginator<int, AssessmentResultData> attempt yang sudah diklaim, terbaru dulu */
    public function paginateClaimed(int $perPage = 20): LengthAwarePaginator;
}
