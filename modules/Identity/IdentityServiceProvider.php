<?php

namespace Modules\Identity;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Identity\Application\EloquentCustomerDirectory;
use Modules\Identity\Contracts\CustomerDirectory;

/**
 * Akun customer portal: guard `customer`, provider & broker `customers` (config/auth.php).
 * Tidak bergantung pada modul lain; modul lain hanya memakai Modules\Identity\Contracts.
 */
final class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CustomerDirectory::class, EloquentCustomerDirectory::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        $byIp = fn (int $perMinute) => fn (Request $request) => Limit::perMinute($perMinute)->by($request->ip());

        // Login juga dibatasi per email+IP di LoginRequest (5 gagal → kunci 60 dtk).
        RateLimiter::for('portal-login', $byIp(20));
        RateLimiter::for('portal-register', $byIp(5));
        RateLimiter::for('portal-password', fn (Request $request) => [
            Limit::perMinute(5)->by('ip:'.$request->ip()),
            Limit::perMinute(3)->by('email:'.mb_strtolower((string) $request->input('email'))),
        ]);
        RateLimiter::for('portal-verification', fn (Request $request) => Limit::perMinute(6)
            ->by((string) ($request->user('customer')?->getAuthIdentifier() ?? $request->ip())));

        Route::middleware('web')->name('portal.')->group(__DIR__.'/routes.php');
    }
}
