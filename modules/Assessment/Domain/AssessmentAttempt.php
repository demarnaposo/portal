<?php

namespace Modules\Assessment\Domain;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Modules\Assessment\Contracts\Data\AssessmentResultData;

/**
 * @property string $id
 * @property string $token_hash
 * @property int $version
 * @property array<string, string> $answers
 * @property list<array{category: array{slug: string, title: string}, score: int}> $scores
 * @property string $recommended_category
 * @property ?string $customer_id
 * @property ?Carbon $claimed_at
 * @property Carbon $created_at
 */
class AssessmentAttempt extends Model
{
    use HasUlids, MassPrunable;

    protected $fillable = ['token_hash', 'version', 'answers', 'scores', 'recommended_category'];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'answers' => 'array',
            'scores' => 'array',
            'claimed_at' => 'datetime',
        ];
    }

    /** Attempt anonim yang tidak pernah diklaim, lebih tua dari masa retensi. */
    public function prunable(): Builder
    {
        return static::whereNull('customer_id')
            ->where('created_at', '<', now()->subDays(config('assessment.anon_retention_days')));
    }

    public function toData(): AssessmentResultData
    {
        return new AssessmentResultData(
            id: $this->id,
            version: $this->version,
            recommendedCategorySlug: $this->recommended_category,
            recommendedCategoryTitle: collect($this->scores)->firstWhere('category.slug', $this->recommended_category)['category']['title'] ?? $this->recommended_category,
            scores: array_map(fn ($s) => ['slug' => $s['category']['slug'], 'title' => $s['category']['title'], 'score' => (int) $s['score']], $this->scores),
            takenAt: $this->created_at->toIso8601String(),
            customerId: $this->customer_id,
        );
    }
}
