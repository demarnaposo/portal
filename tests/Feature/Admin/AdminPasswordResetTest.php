<?php

namespace Tests\Feature\Admin;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia;
use Modules\Admin\Notifications\ResetPasswordNotification;
use Tests\Feature\Identity\CreatesCustomers;
use Tests\TestCase;

class AdminPasswordResetTest extends TestCase
{
    use BrowsesWithCookieJar, CreatesAdmins, CreatesCustomers, RefreshDatabase;

    private const GENERIC = 'Jika email tersebut terdaftar, tautan untuk mengatur ulang kata sandi telah dikirim.';

    public function test_full_reset_flow_still_requires_two_factor(): void
    {
        Notification::fake();
        $admin = $this->admin(attributes: ['email' => 'pemilik@sertifika.test']);

        $this->get('/admin/forgot-password')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('admin/auth/ForgotPassword'));
        $this->post('/admin/forgot-password', ['email' => 'Pemilik@Sertifika.test'])->assertSessionHas('status', self::GENERIC);

        $token = null;
        Notification::assertSentTo($admin, ResetPasswordNotification::class, function (ResetPasswordNotification $n) use (&$token, $admin) {
            $token = $n->token;
            $mail = $n->toMail($admin);
            $this->assertSame('Atur ulang kata sandi panel admin', $mail->subject);
            $this->assertStringContainsString('/admin/reset-password/'.$token.'?email=pemilik%40sertifika.test', $mail->viewData['actionUrl']);

            return true;
        });
        $this->assertSame(1, DB::table('admin_password_reset_tokens')->count());
        $this->assertSame(0, DB::table('customer_password_reset_tokens')->count());

        $this->get('/admin/reset-password/'.$token.'?email=pemilik@sertifika.test')->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('admin/auth/ResetPassword')->where('token', $token));

        $this->post('/admin/reset-password', [
            'token' => $token,
            'email' => 'pemilik@sertifika.test',
            'password' => 'sandi-baru-admin-123',
            'password_confirmation' => 'sandi-baru-admin-123',
        ])->assertRedirect('/admin/login')->assertSessionHas('status');

        $this->assertTrue(Hash::check('sandi-baru-admin-123', $admin->fresh()->password));
        $this->post('/admin/login', ['email' => 'pemilik@sertifika.test', 'password' => 'sandi-baru-admin-123'])
            ->assertRedirect('/admin/two-factor-challenge');
        $this->assertGuest('admin');
    }

    public function test_notification_is_queued(): void
    {
        $this->assertInstanceOf(ShouldQueue::class, new ResetPasswordNotification('t'));
    }

    public function test_unknown_or_customer_email_gets_same_message(): void
    {
        Notification::fake();
        $this->customer(['email' => 'budi@contoh.id']);

        $this->post('/admin/forgot-password', ['email' => 'tidak-ada@sertifika.test'])->assertSessionHas('status', self::GENERIC);
        $this->post('/admin/forgot-password', ['email' => 'budi@contoh.id'])->assertSessionHas('status', self::GENERIC);

        Notification::assertNothingSent();
    }

    public function test_invalid_token_and_weak_password_are_rejected(): void
    {
        $admin = $this->admin(attributes: ['email' => 'pemilik@sertifika.test']);

        $this->post('/admin/reset-password', [
            'token' => 'palsu', 'email' => 'pemilik@sertifika.test', 'password' => 'sandi-baru-admin-123', 'password_confirmation' => 'sandi-baru-admin-123',
        ])->assertSessionHasErrors(['email' => 'Tautan atur ulang kata sandi tidak valid atau sudah kedaluwarsa.']);

        $token = Password::broker('admins')->createToken($admin);
        $this->post('/admin/reset-password', [
            'token' => $token, 'email' => 'pemilik@sertifika.test', 'password' => 'pendek', 'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors(['password' => 'Kata sandi minimal 12 karakter.']);

        $this->assertTrue(Hash::check('sandi-admin-rahasia', $admin->fresh()->password));
    }

    public function test_forgot_password_is_throttled_per_email(): void
    {
        Notification::fake();

        for ($i = 0; $i < 3; $i++) {
            $this->post('/admin/forgot-password', ['email' => 'pemilik@sertifika.test'])->assertRedirect();
        }

        $this->post('/admin/forgot-password', ['email' => 'pemilik@sertifika.test'])->assertStatus(429);
    }

    public function test_reset_signs_out_other_admin_sessions(): void
    {
        $admin = $this->admin(attributes: ['email' => 'pemilik@sertifika.test']);

        // Browser A: sudah login (sandi + 2FA).
        $this->browse('POST', '/admin/login', ['email' => 'pemilik@sertifika.test', 'password' => 'sandi-admin-rahasia']);
        $this->browse('POST', '/admin/two-factor-challenge', ['recovery_code' => 'kode-pemulihan-1'])->assertRedirect('/admin');
        $this->browse('GET', '/admin')->assertOk();
        $browserA = $this->jar;

        // Browser B: reset sandi lewat tautan email.
        $this->jar = [];
        $this->browse('POST', '/admin/reset-password', [
            'token' => Password::broker('admins')->createToken($admin),
            'email' => 'pemilik@sertifika.test',
            'password' => 'sandi-baru-admin-123',
            'password_confirmation' => 'sandi-baru-admin-123',
        ])->assertRedirect('/admin/login');

        $this->jar = $browserA;
        $this->browse('GET', '/admin')->assertRedirect('/admin/login');
        $this->browse('GET', '/admin')->assertRedirect('/admin/login');
    }
}
