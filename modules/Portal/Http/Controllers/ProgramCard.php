<?php

namespace Modules\Portal\Http\Controllers;

/** Props kartu program dari `ProgramSummary` CMS (+ URL halaman program di landing Astro). */
final class ProgramCard
{
    public static function from(array $p): array
    {
        return [
            'slug' => $p['slug'],
            'title' => $p['title'],
            'summary' => $p['summary'] ?? '',
            'issuer' => $p['issuer'],
            'level' => $p['level'] ?? null,
            'duration' => $p['duration'] ?? null,
            'price' => $p['price'],
            'effective_price' => $p['effective_price'],
            'promo_active' => (bool) ($p['promo_active'] ?? false),
            'next_batch_date' => $p['next_batch']['start_date'] ?? null,
            'url' => config('portal.landing_url').'/program/'.rawurlencode($p['slug']),
        ];
    }
}
