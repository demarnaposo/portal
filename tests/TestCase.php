<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    public const CMS = 'http://cms.test/api/v1';

    protected function setUp(): void
    {
        parent::setUp();

        // Tidak ada request ke CMS sungguhan; /site (header/footer portal) selalu tersedia.
        Http::preventStrayRequests();
        Http::fake([self::CMS.'/site' => Http::response(['data' => self::site()])]);
    }

    /** `Site` (kontrak CMS v1.3), subset yang dipakai portal. */
    protected static function site(): array
    {
        return [
            'name' => 'Sertifika',
            'tagline' => 'Pelatihan & sertifikasi profesional',
            'seo' => ['default_og_image_url' => null, 'logo_url' => null],
            'contact' => ['email' => 'halo@sertifika.test', 'phone' => '021-555-0101', 'whatsapp' => '6281200000000', 'address' => 'Jakarta'],
        ];
    }

    /** `ProgramSummary` (kontrak CMS v1.3). */
    protected static function program(string $slug, array $overrides = []): array
    {
        return array_merge([
            'id' => 'prog-'.$slug,
            'slug' => $slug,
            'title' => 'Program '.$slug,
            'sku' => strtoupper($slug),
            'category' => ['slug' => 'sec', 'title' => 'Keamanan', 'description' => null],
            'issuer' => 'bnsp',
            'level' => 'Dasar',
            'duration' => '3 hari',
            'summary' => 'Ringkasan',
            'image_url' => null,
            'price' => 5000000,
            'promo_price' => 4500000,
            'promo_until' => null,
            'promo_active' => true,
            'effective_price' => 4500000,
            'featured' => true,
            'next_batch' => ['id' => 'b-1', 'code' => 'B-01', 'title' => 'B-01', 'start_date' => '2026-11-10', 'end_date' => '2026-11-12',
                'mode' => 'online', 'location' => null, 'capacity' => 20, 'price' => 4500000, 'status' => 'open'],
        ], $overrides);
    }

    /** `AssessmentResult` dari CMS (tanpa attempt_token). */
    protected static function scoreResult(string $recommended = 'sec'): array
    {
        $categories = ['sec' => 'Keamanan', 'pm' => 'Manajemen Proyek'];
        $order = $recommended === 'sec' ? ['sec', 'pm'] : ['pm', 'sec'];

        return [
            'version' => 2,
            'scores' => array_map(fn ($slug, $i) => [
                'category' => ['slug' => $slug, 'title' => $categories[$slug], 'description' => null],
                'score' => 2 - $i,
            ], $order, array_keys($order)),
            'recommended_category' => ['slug' => $recommended, 'title' => $categories[$recommended], 'description' => null],
            'recommendations' => [self::program('sec-a')],
        ];
    }
}
