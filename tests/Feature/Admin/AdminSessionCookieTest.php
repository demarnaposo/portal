<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\Feature\Identity\CreatesCustomers;
use Tests\TestCase;

class AdminSessionCookieTest extends TestCase
{
    use BrowsesWithCookieJar, CreatesAdmins, CreatesCustomers, RefreshDatabase;

    public function test_admin_uses_own_session_and_xsrf_cookie_scoped_to_admin(): void
    {
        $this->browse('GET', '/admin/login')->assertOk();
        $this->assertSame(['XSRF-TOKEN|/admin', 'sertifika-admin-session|/admin'], $this->cookieKeys());

        $this->browse('GET', '/login')->assertOk();
        $this->assertSame(
            ['XSRF-TOKEN|/', 'XSRF-TOKEN|/admin', 'sertifika-admin-session|/admin', 'sertifika-portal-session|/'],
            $this->cookieKeys(),
        );
    }

    public function test_admin_logout_keeps_customer_logged_in(): void
    {
        $this->loginBoth();

        $this->browse('POST', '/admin/logout')->assertRedirect();

        $this->browse('GET', '/admin')->assertRedirect('/admin/login');
        $this->browse('GET', '/api/session')->assertJson(['authenticated' => true]);
    }

    public function test_customer_logout_keeps_admin_logged_in(): void
    {
        $this->loginBoth();

        $this->browse('POST', '/logout')->assertRedirect('/login');

        $this->browse('GET', '/api/session')->assertExactJson(['authenticated' => false]);
        $this->browse('GET', '/admin')->assertOk();
    }

    private function loginBoth(): void
    {
        $this->customer(['email' => 'budi@contoh.id']);
        $admin = $this->admin(attributes: ['email' => 'pemilik@sertifika.test']);

        $this->browse('POST', '/login', ['email' => 'budi@contoh.id', 'password' => 'rahasia-123'])->assertRedirect();
        $this->browse('POST', '/admin/login', ['email' => 'pemilik@sertifika.test', 'password' => 'sandi-admin-rahasia'])
            ->assertRedirect('/admin/two-factor-challenge');
        $code = app(Google2FA::class)->getCurrentOtp(decrypt($admin->two_factor_secret));
        $this->browse('POST', '/admin/two-factor-challenge', ['code' => $code])->assertRedirect('/admin');

        $this->browse('GET', '/api/session')->assertJson(['authenticated' => true]);
        $this->browse('GET', '/admin')->assertOk();
    }

    /** @return list<string> */
    private function cookieKeys(): array
    {
        $keys = array_keys($this->jar);
        sort($keys);

        return $keys;
    }
}
