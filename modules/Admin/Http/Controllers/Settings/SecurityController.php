<?php

namespace Modules\Admin\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;
use Modules\Admin\Http\Requests\PasswordUpdateRequest;
use Modules\Admin\Http\Requests\TwoFactorAuthenticationRequest;

final class SecurityController
{
    public function edit(TwoFactorAuthenticationRequest $request): Response
    {
        $request->ensureStateIsValid();

        return Inertia::render('admin/settings/Security', [
            'canManageTwoFactor' => Features::canManageTwoFactorAuthentication(),
            'twoFactorEnabled' => $request->user('admin')->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
            'passwordRules' => 'minlength: 12;',
            'status' => $request->session()->get('status'),
        ]);
    }

    public function update(PasswordUpdateRequest $request): RedirectResponse
    {
        $request->user('admin')->forceFill(['password' => $request->validated('password'), 'remember_token' => Str::random(60)])->save();
        $request->session()->regenerate();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kata sandi diperbarui.']);

        return back();
    }
}
