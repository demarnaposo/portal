<?php

namespace Modules\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Global (di-prepend, sebelum StartSession): request `/admin*` memakai cookie session sendiri
 * (`session.admin_cookie`) dengan path `/admin`, begitu juga `XSRF-TOKEN`. Logout di satu area
 * tidak lagi mengakhiri session area lain di browser yang sama.
 *
 * Singleton: nilai cookie customer dibaca sekali saat boot dan dipulihkan untuk request non-admin
 * (penting bila beberapa request berjalan di proses yang sama, mis. test).
 */
final class UseAdminSessionCookie
{
    private readonly string $customerCookie;

    private readonly string $customerPath;

    public function __construct()
    {
        $this->customerCookie = config('session.cookie');
        $this->customerPath = config('session.path');
    }

    public function handle(Request $request, Closure $next): Response
    {
        $isAdmin = $request->is('admin', 'admin/*');

        config([
            'session.cookie' => $isAdmin ? config('session.admin_cookie') : $this->customerCookie,
            'session.path' => $isAdmin ? '/admin' : $this->customerPath,
        ]);
        // Store sudah dibuat saat boot (nama dibaca saat itu), jadi ganti juga nama di instance-nya.
        app('session')->driver()->setName(config('session.cookie'));

        return $next($request);
    }
}
