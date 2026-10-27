<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Modules\Assessment\Http\Middleware\CapturePendingClaim;

$isAdmin = fn (Request $request): bool => $request->is('admin', 'admin/*');

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) use ($isAdmin): void {
        $middleware->encryptCookies(except: ['sidebar_state']);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            // `?assessment=<token>` di URL portal mana pun → klaim setelah login/daftar (dilewati di /admin).
            CapturePendingClaim::class,
        ]);

        // Dua area, dua guard: /admin (guard `admin`) dan sisanya portal customer (guard `customer`).
        $middleware->redirectGuestsTo(fn (Request $request) => $isAdmin($request) ? route('login') : route('portal.login'));
        $middleware->redirectUsersTo(fn (Request $request) => $isAdmin($request) ? route('admin.dashboard') : route('portal.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
