<?php

namespace Modules\Admin\Http\Controllers\Settings;

use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Admin\Http\Requests\ProfileUpdateRequest;

final class ProfileController
{
    public function edit(): Response
    {
        return Inertia::render('admin/settings/Profile');
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user('admin')->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Profil disimpan.']);

        return to_route('admin.profile.edit');
    }
}
