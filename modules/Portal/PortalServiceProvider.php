<?php

namespace Modules\Portal;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * UI portal customer (Vue 3 + Inertia, halaman `resources/js/pages/portal`). Komposisi dari modul
 * Identity, Assessment, dan Cms, hanya melalui `Contracts` masing-masing.
 */
final class PortalServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::middleware('web')->group(__DIR__.'/routes.php');
    }
}
