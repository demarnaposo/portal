<?php

namespace Tests\Feature\Assessment;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Modules\Assessment\Contracts\AssessmentResults;
use Modules\Assessment\Domain\AssessmentAttempt;
use Tests\Feature\Identity\CreatesCustomers;
use Tests\TestCase;

class AssessmentClaimTest extends TestCase
{
    use CreatesCustomers, RefreshDatabase;

    private string $recommended = 'sec';

    private function score(string $recommended = 'sec'): string
    {
        // Stub pertama yang cocok menang, jadi semua stub membaca kategori terkini dari properti.
        $this->recommended = $recommended;
        Http::fake([self::CMS.'/internal/assessment/score' => fn () => Http::response(['data' => self::scoreResult($this->recommended)])]);

        return $this->postJson('/api/assessment/score', ['answers' => ['q1' => 'x', 'q2' => 'y']])->assertOk()->json('data.attempt_token');
    }

    private function register(string $email, string $url = '/register'): void
    {
        $this->post($url, [
            'name' => 'Peserta', 'email' => $email, 'phone' => '081234567890',
            'password' => 'kata-sandi-aman', 'password_confirmation' => 'kata-sandi-aman', 'privacy' => true,
        ])->assertRedirect('/email/verify');
    }

    public function test_anonymous_attempt_is_claimed_on_register(): void
    {
        $token = $this->score();

        $this->get('/assessment/claim?token='.$token)->assertRedirect('/register');
        $this->get('/register')->assertInertia(fn (AssertableInertia $page) => $page->where('assessment.pending', true));

        $this->register('baru@contoh.id');

        $customerId = auth('customer')->id();
        $attempt = AssessmentAttempt::sole();
        $this->assertSame($customerId, $attempt->customer_id);
        $this->assertNotNull($attempt->claimed_at);
        $this->assertFalse(session()->has('assessment.pending_token'));

        $results = app(AssessmentResults::class)->forCustomer($customerId);
        $this->assertCount(1, $results);
        $this->assertSame('sec', $results[0]->recommendedCategorySlug);
        $this->assertSame('Keamanan', $results[0]->recommendedCategoryTitle);
    }

    public function test_query_parameter_on_login_claims_after_login_and_dashboard_recommends_from_cms(): void
    {
        $customer = $this->customer(['email' => 'budi@contoh.id']);
        $token = $this->score();

        $this->get('/login?assessment='.$token)->assertOk();
        $this->post('/login', ['email' => 'budi@contoh.id', 'password' => 'rahasia-123'])
            ->assertRedirect('/dashboard')
            ->assertSessionHas('assessment_claimed', true);

        $this->assertSame($customer->id, AssessmentAttempt::sole()->customer_id);

        Http::fake([self::CMS.'/programs*' => Http::response(['data' => [self::program('sec-a'), self::program('sec-b'), self::program('sec-c'), self::program('sec-d')]])]);

        $this->get('/dashboard')->assertInertia(fn (AssertableInertia $page) => $page
            ->component('portal/Dashboard')
            ->where('latestAssessment.recommended_category.slug', 'sec')
            ->has('recommendations', 3)
            ->where('recommendations.0.slug', 'sec-a')
            ->where('recommendations.0.effective_price', 4500000)
            ->where('recommendations.0.url', 'http://localhost:4321/program/sec-a')
            ->where('catalogUnavailable', false)
            ->where('assessment.claimed', true));

        Http::assertSent(fn ($r) => str_starts_with($r->url(), self::CMS.'/programs') && $r['category'] === 'sec');
    }

    public function test_dashboard_survives_cms_outage(): void
    {
        $customer = $this->customer();
        $token = $this->score();
        $this->actingAs($customer, 'customer')->get('/assessment/claim?token='.$token);

        Http::fake([self::CMS.'/programs*' => Http::failedConnection()]);

        $this->actingAs($customer, 'customer')->get('/dashboard')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('recommendations', [])->where('catalogUnavailable', true));
    }

    public function test_logged_in_customer_claims_directly(): void
    {
        $customer = $this->customer();
        $token = $this->score();

        $this->actingAs($customer, 'customer')->get('/assessment/claim?token='.$token)->assertRedirect('/dashboard');

        $this->assertSame($customer->id, AssessmentAttempt::sole()->customer_id);
    }

    public function test_reused_token_does_not_change_owner(): void
    {
        Http::fake([self::CMS.'/programs*' => Http::response(['data' => []])]);
        $owner = $this->customer();
        $token = $this->score();
        $this->actingAs($owner, 'customer')->get('/assessment/claim?token='.$token);
        $claimedAt = AssessmentAttempt::sole()->claimed_at;

        // Customer lain mencoba token yang sama: lewat claim URL, ?assessment=, dan saat daftar.
        $other = $this->customer();
        $this->actingAs($other, 'customer')->get('/assessment/claim?token='.$token)->assertRedirect('/dashboard');
        $this->actingAs($other, 'customer')->get('/dashboard?assessment='.$token)->assertOk();
        auth('customer')->logout();
        $this->register('ketiga@contoh.id', '/register?assessment='.$token);

        $attempt = AssessmentAttempt::sole();
        $this->assertSame($owner->id, $attempt->customer_id);
        $this->assertEquals($claimedAt, $attempt->claimed_at);
        $this->assertSame([], app(AssessmentResults::class)->forCustomer($other->id));
    }

    public function test_unknown_or_malformed_token_is_ignored(): void
    {
        $this->get('/assessment/claim?token=<script>')->assertRedirect('/register');
        $this->assertFalse(session()->has('assessment.pending_token'));

        $this->get('/assessment/claim?token='.str_repeat('a', 48))->assertRedirect('/register');
        $this->register('baru@contoh.id');

        $this->assertSame(0, AssessmentAttempt::count());
    }

    public function test_history_page_lists_results(): void
    {
        $customer = $this->customer();
        $this->actingAs($customer, 'customer')->get('/assessment/claim?token='.$this->score());
        $this->actingAs($customer, 'customer')->get('/assessment/claim?token='.$this->score('pm'));

        $this->actingAs($customer, 'customer')->get('/assessments')
            ->assertInertia(fn (AssertableInertia $page) => $page->component('portal/Assessments')
                ->has('results', 2)
                ->where('results.0.recommended_category.slug', 'pm'));
    }
}
