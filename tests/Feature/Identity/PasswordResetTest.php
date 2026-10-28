<?php

namespace Tests\Feature\Identity;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Modules\Identity\Notifications\ResetPasswordNotification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use CreatesCustomers, RefreshDatabase;

    public function test_full_reset_flow(): void
    {
        Notification::fake();
        $customer = $this->customer(['email' => 'budi@contoh.id']);

        $this->get('/forgot-password')->assertOk();
        $this->post('/forgot-password', ['email' => 'budi@contoh.id'])->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($customer, ResetPasswordNotification::class, function ($n) use (&$token, $customer) {
            $token = $n->token;
            $this->assertStringContainsString('/reset-password/'.$token, $n->toMail($customer)->viewData['actionUrl']);

            return true;
        });

        $this->get('/reset-password/'.$token.'?email=budi@contoh.id')->assertOk();

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'budi@contoh.id',
            'password' => 'sandi-baru-123',
            'password_confirmation' => 'sandi-baru-123',
        ])->assertRedirect('/login');

        $this->assertTrue(Hash::check('sandi-baru-123', $customer->fresh()->password));
    }

    public function test_unknown_email_gets_same_message(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'tidak-ada@contoh.id'])
            ->assertSessionHas('status', 'Jika email tersebut terdaftar, tautan untuk mengatur ulang kata sandi telah dikirim.');
        Notification::assertNothingSent();
    }

    public function test_invalid_token_is_rejected(): void
    {
        $customer = $this->customer(['email' => 'budi@contoh.id']);

        $this->post('/reset-password', [
            'token' => 'palsu', 'email' => 'budi@contoh.id', 'password' => 'sandi-baru-123', 'password_confirmation' => 'sandi-baru-123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('rahasia-123', $customer->fresh()->password));
    }

    public function test_customer_and_admin_brokers_are_separate(): void
    {
        $this->assertSame('admin_password_reset_tokens', config('auth.passwords.admins.table'));
        $this->assertSame('customers', config('auth.passwords.customers.provider'));
        $this->assertSame('customer_password_reset_tokens', config('auth.passwords.customers.table'));
    }
}
