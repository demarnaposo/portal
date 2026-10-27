<?php

namespace Modules\Cms;

use Illuminate\Support\ServiceProvider;
use Modules\Cms\Application\HttpCmsApi;
use Modules\Cms\Contracts\CmsApi;

/** Klien API CMS; modul lain hanya memakai Modules\Cms\Contracts. */
final class CmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CmsApi::class, HttpCmsApi::class);
    }
}
