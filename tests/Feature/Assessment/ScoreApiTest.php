<?php

namespace Tests\Feature\Assessment;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Modules\Assessment\Domain\AssessmentAttempt;
use Tests\TestCase;

class ScoreApiTest extends TestCase
{
    use RefreshDatabase;

    private const ANSWERS = ['q1' => 'q1b', 'q2' => 'q2b'];

    public function test_scores_via_cms_and_stores_attempt_with_hashed_token_only(): void
    {
        Http::fake([self::CMS.'/internal/assessment/score' => Http::response(['data' => self::scoreResult()])]);

        $response = $this->postJson('/api/assessment/score', ['answers' => self::ANSWERS], ['Origin' => 'http://localhost:4321'])
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:4321')
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('data.version', 2)
            ->assertJsonPath('data.recommended_category.slug', 'sec')
            ->assertJsonPath('data.scores.0.category.title', 'Keamanan')
            ->assertJsonPath('data.recommendations.0.slug', 'sec-a');

        $token = $response->json('data.attempt_token');
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{48}$/', $token);

        Http::assertSent(fn (Request $r) => $r->url() === self::CMS.'/internal/assessment/score'
            && $r->method() === 'POST'
            && $r->hasHeader('Authorization', 'Bearer test-service-token')
            && json_decode($r->body(), true)['answers'] === self::ANSWERS);

        $attempt = AssessmentAttempt::sole();
        $this->assertSame(hash('sha256', $token), $attempt->token_hash);
        $this->assertSame(self::ANSWERS, $attempt->answers);
        $this->assertSame('sec', $attempt->recommended_category);
        $this->assertSame(2, $attempt->version);
        $this->assertNull($attempt->customer_id);
        $this->assertDatabaseMissing('assessment_attempts', ['token_hash' => $token]);
    }

    public function test_cms_validation_errors_are_passed_through_without_storing(): void
    {
        $errors = ['message' => 'Pertanyaan ini wajib dijawab.', 'errors' => ['answers.q2' => ['Pertanyaan ini wajib dijawab.']]];
        Http::fake([self::CMS.'/internal/assessment/score' => Http::response($errors, 422)]);

        $this->postJson('/api/assessment/score', ['answers' => ['q1' => 'q1b']])
            ->assertStatus(422)
            ->assertExactJson($errors);

        $this->assertSame(0, AssessmentAttempt::count());
    }

    public function test_cms_404_is_passed_through(): void
    {
        Http::fake([self::CMS.'/internal/assessment/score' => Http::response(['message' => 'Asesmen belum tersedia.'], 404)]);

        $this->postJson('/api/assessment/score', ['answers' => self::ANSWERS])->assertNotFound()->assertJsonPath('message', 'Asesmen belum tersedia.');
    }

    public function test_cms_down_returns_503(): void
    {
        Http::fake([self::CMS.'/internal/assessment/score' => Http::failedConnection()]);

        $this->postJson('/api/assessment/score', ['answers' => self::ANSWERS])
            ->assertStatus(503)
            ->assertJsonPath('message', 'Layanan assessment sedang tidak tersedia. Coba lagi beberapa saat lagi.');

        $this->assertSame(0, AssessmentAttempt::count());
    }

    public function test_malformed_payload_is_rejected_before_calling_cms(): void
    {
        $this->postJson('/api/assessment/score', ['answers' => 'x'])->assertStatus(422)->assertJsonValidationErrors('answers');
        $this->postJson('/api/assessment/score', ['answers' => ['bad key!' => 'a']])->assertStatus(422)->assertJsonValidationErrors('answers');
        $this->postJson('/api/assessment/score', ['answers' => ['q1' => str_repeat('a', 101)]])->assertStatus(422);

        Http::assertNothingSent();
    }

    public function test_cors_preflight_allows_landing_origin_only(): void
    {
        $preflight = ['Origin' => 'http://localhost:4321', 'Access-Control-Request-Method' => 'POST', 'Access-Control-Request-Headers' => 'content-type'];

        $this->options('/api/assessment/score', [], $preflight)
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:4321');

        $evil = $this->options('/api/assessment/score', [], ['Origin' => 'https://jahat.example'] + $preflight);
        $this->assertNotSame('https://jahat.example', $evil->headers->get('Access-Control-Allow-Origin'));
    }

    public function test_is_rate_limited_per_ip(): void
    {
        config(['assessment.score_rate_limit' => 2]);
        Http::fake([self::CMS.'/internal/assessment/score' => Http::response(['data' => self::scoreResult()])]);

        $this->postJson('/api/assessment/score', ['answers' => self::ANSWERS])->assertOk();
        $this->postJson('/api/assessment/score', ['answers' => self::ANSWERS])->assertOk();
        $this->postJson('/api/assessment/score', ['answers' => self::ANSWERS])->assertStatus(429);
    }
}
