<?php

namespace Modules\Admin;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;
use Laravel\Fortify\Fortify;
use Modules\Admin\Console\CreateAdminCommand;
use Modules\Admin\Domain\Admin;
use Modules\Admin\Http\Responses\LoginResponse;

/**
 * Panel admin (Vue + Inertia, halaman `resources/js/pages/admin`): guard `admin`, tabel `admins`,
 * login & 2FA TOTP lewat Fortify (config/fortify.php: guard admin, prefix admin). Data customer &
 * assessment hanya dibaca lewat Contracts modul Identity & Assessment.
 */
final class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(TwoFactorLoginResponse::class, LoginResponse::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([CreateAdminCommand::class]);
        }

        $this->configureFortify();

        // `auth:admin` menjadikan `admin` guard aktif, sehingga $request->user() & Gate memakai Admin.
        Gate::define('viewCustomers', fn (Admin $admin) => true);
        Gate::define('viewAdmins', fn (Admin $admin) => $admin->isOwner());

        Route::middleware(['web', 'auth:admin'])->prefix('admin')->name('admin.')->group(__DIR__.'/routes.php');
    }

    private function configureFortify(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('admin/auth/Login', [
            'status' => $request->session()->get('status'),
        ]));
        Fortify::twoFactorChallengeView(fn () => Inertia::render('admin/auth/TwoFactorChallenge'));
        Fortify::confirmPasswordView(fn () => Inertia::render('admin/auth/ConfirmPassword'));

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by(Str::transliterate(Str::lower((string) $request->input(Fortify::username())).'|'.$request->ip())));
        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)->by($request->session()->get('login.id')));
    }
}
