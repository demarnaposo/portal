<?php

namespace Modules\Admin\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse;

/** Seperti respons Fortify, tetapi `url.intended` hanya dipakai bila mengarah ke area /admin. */
final class LoginResponse implements LoginResponseContract, TwoFactorLoginResponse
{
    public function toResponse($request): RedirectResponse
    {
        $intended = (string) $request->session()->pull('url.intended', '');
        $admin = url('/admin');

        return redirect()->to($intended === $admin || str_starts_with($intended, $admin.'/') ? $intended : route('admin.dashboard'));
    }
}
