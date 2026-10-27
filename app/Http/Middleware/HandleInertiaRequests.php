<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Middleware;
use Modules\Admin\Domain\Admin;
use Modules\Cms\Contracts\CmsApi;
use Modules\Cms\Contracts\CmsRequestFailed;
use Modules\Identity\Contracts\CustomerDirectory;

/** Props bersama dua area: portal customer (header/footer ala landing) dan panel /admin. */
class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function __construct(
        private readonly CustomerDirectory $customers,
        private readonly CmsApi $cms,
    ) {}

    public function share(Request $request): array
    {
        $shared = [
            ...parent::share($request),
            'name' => config('app.name'),
            'landingUrl' => config('portal.landing_url'),
            'flash' => ['status' => fn () => $request->session()->get('status')],
        ];

        if ($request->is('admin', 'admin/*')) {
            $admin = $request->user('admin');

            return [
                ...$shared,
                'auth' => ['user' => $admin instanceof Admin ? [
                    'id' => $admin->id,
                    'name' => $admin->name,
                    'email' => $admin->email,
                    'role' => $admin->role->value,
                    'two_factor_enabled' => $admin->hasEnabledTwoFactorAuthentication(),
                ] : null],
                'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            ];
        }

        $customer = $this->customers->current();

        return [
            ...$shared,
            // Sengaja hanya field non-sensitif (tanpa NPWP/alamat); profil lengkap hanya di halaman profil.
            'auth' => ['customer' => $customer ? [
                'name' => $customer->name,
                'email' => $customer->email,
                'email_verified' => $customer->emailVerified,
            ] : null],
            'site' => fn () => $this->site(),
        ];
    }

    /**
     * Nama situs & kontak untuk header/footer (sama dengan landing). CMS mati → fallback tanpa kontak.
     *
     * @return array{name: string, tagline: ?string, logo_url: ?string, contact: ?array<string, string>}
     */
    private function site(): array
    {
        try {
            $site = $this->cms->site();
        } catch (CmsRequestFailed $e) {
            Log::warning('Data situs CMS gagal dimuat', ['error' => $e->getMessage()]);

            return ['name' => config('mail.from.name'), 'tagline' => null, 'logo_url' => null, 'contact' => null];
        }

        return [
            'name' => $site['name'],
            'tagline' => $site['tagline'] ?? null,
            'logo_url' => $site['seo']['logo_url'] ?? null,
            'contact' => $site['contact'] ?? null,
        ];
    }
}
