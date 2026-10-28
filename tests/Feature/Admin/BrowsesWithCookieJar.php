<?php

namespace Tests\Feature\Admin;

use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * Request seperti browser sungguhan: driver session `database`, cookie dikirim balik sesuai path.
 * Ganti `$jar` untuk mensimulasikan browser lain.
 */
trait BrowsesWithCookieJar
{
    /** @var array<string, Cookie> cookie browser, kunci `nama|path` */
    protected array $jar = [];

    protected function setUpBrowsesWithCookieJar(): void
    {
        // Driver nyata (bukan array) agar tiap session benar-benar terpisah per cookie.
        config(['session.driver' => 'database']);
    }

    /**
     * Request seperti browser: kirim cookie yang path-nya cocok (yang paling spesifik menang untuk nama
     * yang sama), simpan Set-Cookie. Session store & guard dibuat ulang seperti proses PHP baru.
     *
     * @param  array<string, mixed>  $data
     */
    protected function browse(string $method, string $uri, array $data = []): TestResponse
    {
        $this->app['session']->forgetDrivers();
        $this->app->forgetInstance('session.store');
        $this->app['auth']->forgetGuards();

        $cookies = collect($this->jar)
            ->filter(fn (Cookie $c) => $c->getPath() === '/' || $uri === $c->getPath() || str_starts_with($uri, $c->getPath().'/'))
            ->sortBy(fn (Cookie $c) => strlen($c->getPath()))
            ->mapWithKeys(fn (Cookie $c) => [$c->getName() => $c->getValue()])
            ->all();

        $response = $this->call($method, $uri, $data, $cookies);

        foreach ($response->baseResponse->headers->getCookies() as $cookie) {
            $key = $cookie->getName().'|'.$cookie->getPath();

            if ($cookie->isCleared()) {
                unset($this->jar[$key]);
            } else {
                $this->jar[$key] = $cookie;
            }
        }

        return $response;
    }
}
