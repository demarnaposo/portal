<?php

namespace Modules\Cms\Application;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Cms\Contracts\CmsApi;
use Modules\Cms\Contracts\CmsRequestFailed;

final class HttpCmsApi implements CmsApi
{
    private const SITE_TTL_SECONDS = 600;

    public function site(): array
    {
        return Cache::remember('cms.site', self::SITE_TTL_SECONDS, fn () => $this->data($this->send('GET', '/site')));
    }

    public function programs(array $query = []): array
    {
        return array_values($this->data($this->send('GET', '/programs', $query)));
    }

    public function batch(string $id): ?array
    {
        $response = $this->send('GET', '/batches/'.rawurlencode($id), allow404: true);

        return $response->status() === 404 ? null : $this->data($response);
    }

    public function scoreAssessment(array $answers): array
    {
        return $this->data($this->send('POST', '/internal/assessment/score', ['answers' => (object) $answers], withToken: true));
    }

    /** @param  array<string, mixed>|object  $payload */
    private function send(string $method, string $path, array|object $payload = [], bool $withToken = false, bool $allow404 = false): Response
    {
        $url = config('services.cms.url').$path;

        try {
            $response = $this->client($withToken)->send($method, $url, $method === 'GET' ? ['query' => $payload] : ['json' => $payload]);
        } catch (ConnectionException $e) {
            throw new CmsRequestFailed("CMS tidak dapat dihubungi ({$method} {$url}): {$e->getMessage()}");
        }

        if ($response->successful() || ($allow404 && $response->status() === 404)) {
            return $response;
        }

        throw new CmsRequestFailed("CMS membalas HTTP {$response->status()} untuk {$method} {$url}", $response->status(), (array) $response->json());
    }

    private function client(bool $withToken): PendingRequest
    {
        $client = Http::acceptJson()->timeout(config('services.cms.timeout'))->connectTimeout(3);

        return $withToken ? $client->withToken((string) config('services.cms.token')) : $client;
    }

    /** @return array<string, mixed> isi `data` respons CMS (bentuk sesuai kontrak endpoint) */
    private function data(Response $response): array
    {
        $data = $response->json('data');

        if (! is_array($data)) {
            throw new CmsRequestFailed('Respons CMS tidak sesuai format (tanpa "data"): '.$response->effectiveUri());
        }

        return $data;
    }
}
