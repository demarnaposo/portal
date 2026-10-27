<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Modules\Admin\Domain\Admin;
use PragmaRX\Google2FA\Google2FA;
use Tests\Feature\Identity\CreatesCustomers;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use CreatesAdmins, CreatesCustomers, RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('admin/auth/Login'));
    }

    public function test_guest_is_sent_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/customers')->assertRedirect('/admin/login');
    }

    public function test_admin_with_two_factor_must_pass_challenge(): void
    {
        $admin = $this->admin(attributes: ['email' => 'pemilik@sertifika.test']);

        $this->post('/admin/login', ['email' => 'PEMILIK@sertifika.test', 'password' => 'sandi-admin-rahasia'])
            ->assertRedirect('/admin/two-factor-challenge');
        $this->assertGuest('admin');

        $code = app(Google2FA::class)->getCurrentOtp(decrypt($admin->two_factor_secret));
        $this->post('/admin/two-factor-challenge', ['code' => $code])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('customer');
        $this->get('/admin')->assertOk()->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/Dashboard')
            ->where('auth.user.email', 'pemilik@sertifika.test'));
    }

    public function test_admin_without_two_factor_is_forced_to_enable_it(): void
    {
        $admin = $this->admin(twoFactor: false, attributes: ['email' => 'baru@sertifika.test']);

        $this->post('/admin/login', ['email' => 'baru@sertifika.test', 'password' => 'sandi-admin-rahasia'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($admin, 'admin');

        $this->get('/admin')->assertRedirect('/admin/settings/security');
        $this->get('/admin/customers')->assertRedirect('/admin/settings/security');

        // Halaman keamanan meminta konfirmasi sandi, lalu aktifkan + konfirmasi TOTP lewat endpoint Fortify.
        $this->get('/admin/settings/security')->assertRedirect('/admin/user/confirm-password');
        $this->post('/admin/user/confirm-password', ['password' => 'sandi-admin-rahasia'])->assertRedirect();
        $this->get('/admin/settings/security')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('admin/settings/Security')->where('twoFactorEnabled', false));

        $this->post('/admin/user/two-factor-authentication')->assertRedirect();
        $secret = decrypt($admin->fresh()->two_factor_secret);
        $this->post('/admin/user/confirmed-two-factor-authentication', ['code' => app(Google2FA::class)->getCurrentOtp($secret)])
            ->assertSessionHasNoErrors();

        $this->assertTrue($admin->fresh()->hasEnabledTwoFactorAuthentication());
        $this->get('/admin')->assertOk();
    }

    public function test_admin_login_is_throttled(): void
    {
        $this->admin(attributes: ['email' => 'pemilik@sertifika.test']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'pemilik@sertifika.test', 'password' => 'salah']);
        }

        $this->post('/admin/login', ['email' => 'pemilik@sertifika.test', 'password' => 'sandi-admin-rahasia'])->assertStatus(429);
    }

    public function test_customer_cannot_open_admin_and_admin_is_not_a_customer(): void
    {
        $customer = $this->customer(['email' => 'budi@contoh.id']);

        $this->actingAs($customer, 'customer')->get('/admin')->assertRedirect('/admin/login');
        $this->actingAs($customer, 'customer')->get('/admin/customers')->assertRedirect('/admin/login');
        // Kredensial customer tidak berlaku di login admin.
        auth('customer')->logout();
        $this->post('/admin/login', ['email' => 'budi@contoh.id', 'password' => 'rahasia-123'])->assertSessionHasErrors('email');
        $this->assertGuest('admin');

        $admin = $this->admin(attributes: ['email' => 'admin@sertifika.test']);
        $this->actingAs($admin, 'admin')->get('/dashboard')->assertRedirect('/login');
        $this->actingAs($admin, 'admin')->getJson('/api/session')->assertExactJson(['authenticated' => false]);
        $this->post('/login', ['email' => 'admin@sertifika.test', 'password' => 'sandi-admin-rahasia'])->assertSessionHasErrors('email');
        $this->assertGuest('customer');
    }

    public function test_admin_login_ignores_intended_url_outside_admin(): void
    {
        $this->admin(twoFactor: false, attributes: ['email' => 'pemilik@sertifika.test']);

        session(['url.intended' => url('/dashboard')]);
        $this->post('/admin/login', ['email' => 'pemilik@sertifika.test', 'password' => 'sandi-admin-rahasia'])->assertRedirect('/admin');
    }

    public function test_create_admin_command(): void
    {
        $this->artisan('admin:create', ['email' => 'Pemilik@Sertifika.test', '--name' => 'Pemilik', '--no-interaction' => true])
            ->expectsOutputToContain('Kata sandi sementara:')
            ->assertSuccessful();

        $admin = Admin::sole();
        $this->assertSame('pemilik@sertifika.test', $admin->email);
        $this->assertTrue($admin->isOwner());
        $this->assertFalse($admin->hasEnabledTwoFactorAuthentication());

        $this->artisan('admin:create', ['email' => 'pemilik@sertifika.test', '--no-interaction' => true])->assertFailed();
        $this->artisan('admin:create', ['email' => 'op@sertifika.test', '--role' => 'super', '--no-interaction' => true])->assertFailed();
        $this->assertSame(1, Admin::count());
    }
}
