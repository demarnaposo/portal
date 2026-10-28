<?php

namespace Modules\Assessment\Application;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Assessment\Contracts\AssessmentResults;
use Modules\Assessment\Domain\AssessmentAttempt;

/** Menyimpan attempt anonim (token acak, hanya hash di DB) dan mengklaimnya ke customer. */
final class AttemptStore implements AssessmentResults
{
    /**
     * @param  array<string, string>  $answers
     * @param  array<string, mixed>  $result  `AssessmentResult` dari CMS: version, scores[{category{slug, title}, score}], recommended_category{slug}
     * @return string attempt_token (plaintext, hanya dikembalikan sekali)
     */
    public function record(array $answers, array $result): string
    {
        $token = Str::random(48);

        AssessmentAttempt::create([
            'token_hash' => self::hash($token),
            'version' => (int) $result['version'],
            'answers' => $answers,
            'scores' => array_map(fn ($s) => [
                'category' => ['slug' => (string) $s['category']['slug'], 'title' => (string) $s['category']['title']],
                'score' => (int) $s['score'],
            ], $result['scores']),
            'recommended_category' => (string) $result['recommended_category']['slug'],
        ]);

        return $token;
    }

    /**
     * Klaim sekali saja: hanya attempt yang belum punya pemilik yang diperbarui (atomik).
     * Token yang tidak dikenal atau sudah diklaim (siapa pun) diabaikan.
     */
    public function claim(string $token, string $customerId): bool
    {
        return AssessmentAttempt::query()
            ->where('token_hash', self::hash($token))
            ->whereNull('customer_id')
            ->update(['customer_id' => $customerId, 'claimed_at' => now(), 'updated_at' => now()]) === 1;
    }

    public function forCustomer(string $customerId): array
    {
        return array_values(AssessmentAttempt::query()
            ->where('customer_id', $customerId)
            ->latest()->orderByDesc('id')
            ->get()
            ->map(fn (AssessmentAttempt $a) => $a->toData())
            ->all());
    }

    public function count(bool $claimedOnly = false): int
    {
        return AssessmentAttempt::query()->when($claimedOnly, fn ($q) => $q->whereNotNull('customer_id'))->count();
    }

    public function paginateClaimed(int $perPage = 20): LengthAwarePaginator
    {
        return AssessmentAttempt::query()
            ->whereNotNull('customer_id')
            ->latest()->orderByDesc('id')
            ->paginate($perPage)
            ->through(fn (AssessmentAttempt $a) => $a->toData());
    }

    private static function hash(string $token): string
    {
        return hash('sha256', $token);
    }
}
