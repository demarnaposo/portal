<?php

namespace Modules\Admin\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Modules\Admin\Domain\Admin;

final class AdminController
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/admins/Index', [
            'admins' => Admin::query()->orderBy('name')->get()->map(fn (Admin $a) => [
                'id' => $a->id,
                'name' => $a->name,
                'email' => $a->email,
                'role' => $a->role->label(),
                'two_factor_enabled' => $a->hasEnabledTwoFactorAuthentication(),
                'created_at' => $a->created_at->toIso8601String(),
            ]),
        ]);
    }
}
