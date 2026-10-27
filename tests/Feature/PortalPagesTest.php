<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Modules\Cms\Contracts\CmsApi;
use Modules\Cms\Contracts\CmsRequestFailed;
use Tests\Feature\Identity\CreatesCustomers;
use Tests\TestCase;

class PortalPagesTest extends TestCase
{
    use CreatesCustomers, RefreshDatabase;

    public function test_dashboard_without_assessment_shares_site_from_cms(): void
    {
        $this->actingAs($this->customer(['name' => 'Rina Wati']), 'customer')->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('portal/Dashboard')
                ->where('latestAssessment', null)
                ->where('recommendations', [])
                ->where('auth.customer.name', 'Rina Wati')
                ->where('landingUrl', 'http://localhost:4321')
                ->where('site.name', 'Sertifika')
                ->where('site.contact.email', 'halo@sertifika.test'));
    }

    public function test_pages_render_when_cms_site_is_down(): void
    {
        Http::preventStrayRequests(false);
        $this->swap(CmsApi::class, new class implements CmsApi
        {
            public function site(): array
            {
                throw new CmsRequestFailed('down');
            }

            public function programs(array $query = []): array
            {
                return [];
            }

            public function batch(string $id): ?array
            {
                return null;
            }

            public function scoreAssessment(array $answers): array
            {
                return [];
            }
        });

        $this->get('/login')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('site.name', 'Sertifika')->where('site.contact', null));
    }

    public function test_coming_soon_pages(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer, 'customer')->get('/orders')
            ->assertInertia(fn (AssertableInertia $page) => $page->component('portal/ComingSoon')->where('section', 'orders'));
        $this->actingAs($customer, 'customer')->get('/programs')
            ->assertInertia(fn (AssertableInertia $page) => $page->component('portal/ComingSoon')->where('section', 'programs'));
    }

    public function test_checkout_shows_batch_summary_with_cms_price(): void
    {
        Http::fake([self::CMS.'/batches/b-1' => Http::response(['data' => [
            'id' => 'b-1', 'code' => 'PMP-01', 'title' => 'PMP-01', 'start_date' => '2026-11-10', 'end_date' => '2026-11-12',
            'mode' => 'online', 'location' => null, 'capacity' => 20, 'seats_taken' => 0, 'seats_left' => 20,
            'price' => 4000000, 'status' => 'open', 'program' => self::program('pmp'),
        ]])]);

        $this->get('/checkout?batch=b-1&qty=3')->assertRedirect('/login');

        $this->actingAs($this->customer(), 'customer')->get('/checkout?batch=b-1&qty=3&price=1')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('portal/Checkout')
                ->where('qty', 3)
                ->where('batch.code', 'PMP-01')
                ->where('batch.price', 4000000)
                ->where('batch.total', 12000000)
                ->where('batch.program.url', 'http://localhost:4321/program/pmp')
                ->where('unavailable', false));
    }

    public function test_checkout_with_unknown_batch_or_cms_down(): void
    {
        Http::fake([
            self::CMS.'/batches/tidak-ada' => Http::response(['message' => 'Batch tidak ditemukan.'], 404),
            self::CMS.'/batches/b-2' => Http::failedConnection(),
        ]);
        $customer = $this->customer();

        $this->actingAs($customer, 'customer')->get('/checkout?batch=tidak-ada&qty=999')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->where('batch', null)->where('qty', 50)->where('unavailable', false));

        $this->actingAs($customer, 'customer')->get('/checkout?batch=b-2')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('batch', null)->where('unavailable', true));

        // Id aneh tidak diteruskan ke CMS.
        $this->actingAs($customer, 'customer')->get('/checkout?batch=../site')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('batch', null));
        Http::assertNotSent(fn ($r) => str_contains($r->url(), '..'));
    }

    public function test_session_endpoint_for_landing_header(): void
    {
        $origin = ['Origin' => 'http://localhost:4321'];

        $this->getJson('/api/session', $origin)
            ->assertOk()
            ->assertExactJson(['authenticated' => false])
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:4321')
            ->assertHeader('Access-Control-Allow-Credentials', 'true')
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->actingAs($this->customer(['name' => 'Rina Wati', 'npwp' => '0123456789012345']), 'customer')
            ->getJson('/api/session', $origin)
            ->assertExactJson(['authenticated' => true, 'name' => 'Rina Wati']);
    }
}
