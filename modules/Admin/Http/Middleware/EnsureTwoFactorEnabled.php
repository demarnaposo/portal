<?php

namespace Modules\Admin\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Admin\Domain\Admin;
use Symfony\Component\HttpFoundation\Response;

/** 2FA wajib: admin tanpa 2FA terkonfirmasi hanya boleh membuka halaman Keamanan untuk mengaktifkannya. */
final class EnsureTwoFactorEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = $request->user('admin');

        if ($admin instanceof Admin && ! $admin->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('admin.security.edit')
                ->with('status', 'Aktifkan autentikasi dua faktor (2FA) untuk melanjutkan.');
        }

        return $next($request);
    }
}
