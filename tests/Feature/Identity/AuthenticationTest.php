<?php

namespace Tests\Feature\Identity;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Inertia\Testing\AssertableInertia;
use Modules\Identity\Contracts\Events\CustomerAuthenticated;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use CreatesCustomers, RefreshDatabase;

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('portal/auth/Login'));
    }

    public function test_root_redirects_guest_to_login_via_dashboard(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_customer_can_login_with_session_regenerated_and_event(): void
    {
        Event::fake([CustomerAuthenticated::class]);
        $customer = $this->customer(['email' => 'budi@contoh.id']);

        $this->startSession();
        $before = session()->getId();

        $this->post('/login', ['email' => 'BUDI@contoh.id', 'password' => 'rahasia-123', 'remember' => true])
            ->assertRedirect('/dashboard')
            ->assertCookie(auth('customer')->getRecallerName());

        $this->assertAuthenticatedAs($customer, 'customer');
        $this->assertNotSame($before, session()->getId());
        Event::assertDispatched(CustomerAuthenticated::class, fn ($e) => $e->customerId === $customer->id);
    }

    public function test_login_redirects_to_intended_portal_url_only(): void
    {
        $this->customer(['email' => 'budi@contoh.id']);

        $this->get('/checkout?batch=b1&qty=2')->assertRedirect('/login');
        $this->post('/login', ['email' => 'budi@contoh.id', 'password' => 'rahasia-123'])
            ->assertRedirect('/checkout?batch=b1&qty=2');

        // url.intended ke area admin diabaikan.
        auth('customer')->logout();
        session(['url.intended' => url('/admin/customers')]);
        $this->post('/login', ['email' => 'budi@contoh.id', 'password' => 'rahasia-123'])
            ->assertRedirect('/dashboard');
    }

    public function test_wrong_password_fails(): void
    {
        $this->customer(['email' => 'budi@contoh.id']);

        $this->from('/login')->post('/login', ['email' => 'budi@contoh.id', 'password' => 'salah'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        $this->assertGuest('customer');
    }

    public function test_login_is_throttled_per_email_and_ip(): void
    {
        $this->customer(['email' => 'budi@contoh.id']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'budi@contoh.id', 'password' => 'salah']);
        }

        // Kata sandi benar pun ditolak selama terkunci.
        $this->post('/login', ['email' => 'budi@contoh.id', 'password' => 'rahasia-123'])
            ->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak percobaan', session('errors')->first('email'));
        $this->assertGuest('customer');

        // Email lain dari IP yang sama tidak ikut terkunci.
        $this->customer(['email' => 'lain@contoh.id']);
        $this->post('/login', ['email' => 'lain@contoh.id', 'password' => 'rahasia-123'])
            ->assertRedirect('/dashboard');
    }

    public function test_logout(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer, 'customer')->post('/logout')->assertRedirect('/login');
        $this->assertGuest('customer');
    }

    public function test_authenticated_customer_cannot_see_guest_pages(): void
    {
        $this->actingAs($this->customer(), 'customer')->get('/login')->assertRedirect('/dashboard');
    }
}
