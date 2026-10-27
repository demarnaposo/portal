<?php

namespace Modules\Assessment\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Modules\Assessment\Application\AttemptStore;
use Modules\Assessment\Http\Requests\ScoreAssessmentRequest;
use Modules\Cms\Contracts\CmsApi;
use Modules\Cms\Contracts\CmsRequestFailed;

final class ScoreController
{
    /** POST /api/assessment/score → `AssessmentResult` (kontrak CMS v1.3) + `attempt_token`. */
    public function __invoke(ScoreAssessmentRequest $request, CmsApi $cms, AttemptStore $attempts): JsonResponse
    {
        try {
            $result = $cms->scoreAssessment($request->answers());
        } catch (CmsRequestFailed $e) {
            // 422 (jawaban tidak valid) & 404 (assessment belum tersedia) dari CMS diteruskan apa adanya.
            if (in_array($e->status, [404, 422], true)) {
                return $this->noStore(response()->json($e->body, $e->status));
            }

            Log::warning('Scoring assessment gagal', ['error' => $e->getMessage()]);

            return $this->noStore(response()->json(['message' => 'Layanan assessment sedang tidak tersedia. Coba lagi beberapa saat lagi.'], 503));
        }

        return $this->noStore(response()->json(['data' => [
            ...$result,
            'attempt_token' => $attempts->record($request->answers(), $result),
        ]]));
    }

    private function noStore(JsonResponse $response): JsonResponse
    {
        return $response->header('Cache-Control', 'no-store, private');
    }
}
