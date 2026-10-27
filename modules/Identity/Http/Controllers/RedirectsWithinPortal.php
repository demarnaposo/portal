<?php

namespace Modules\Identity\Http\Controllers;

use Illuminate\Http\RedirectResponse;

trait RedirectsWithinPortal
{
    /**
     * Seperti redirect()->intended(), tetapi tidak ke area admin: `url.intended` dipakai bersama
     * guard `admin`, jadi customer yang sempat membuka /admin tidak dilempar ke sana.
     */
    protected function redirectToIntended(): RedirectResponse
    {
        $intended = (string) session()->pull('url.intended', '');
        $base = rtrim(url('/'), '/');
        $admin = $base.'/admin';

        $allowed = str_starts_with($intended, $base.'/') && $intended !== $admin && ! str_starts_with($intended, $admin.'/');

        return redirect()->to($allowed ? $intended : route('portal.dashboard'));
    }
}
