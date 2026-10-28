<?php

namespace Modules\Cms\Contracts;

/**
 * Klien server-to-server ke API CMS (kontrak `docs/api-contract.md` di repo CMS). Array mengikuti bentuk
 * kontrak apa adanya. Harga tidak pernah di-cache di sini; CMS adalah sumber kebenaran harga.
 *
 * @throws CmsRequestFailed bila CMS tidak terjangkau, timeout, atau membalas error
 */
interface CmsApi
{
    /**
     * `Site` (nama, kontak, logo) — boleh di-cache singkat.
     *
     * @return array<string, mixed>
     */
    public function site(): array;

    /**
     * @param  array<string, scalar>  $query  filter `/programs` (mis. `category`)
     * @return list<array<string, mixed>> `ProgramSummary[]` (featured dulu)
     */
    public function programs(array $query = []): array;

    /**
     * `BatchDetail`, atau null bila batch tidak ada/tidak published (404). Tidak di-cache.
     *
     * @return array<string, mixed>|null
     */
    public function batch(string $id): ?array;

    /**
     * Scoring di CMS (pemilik bobot), tanpa menyimpan apa pun: `AssessmentResult` tanpa `attempt_token`.
     *
     * @param  array<string, string>  $answers
     * @return array<string, mixed>
     */
    public function scoreAssessment(array $answers): array;
}
