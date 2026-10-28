<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Admin\Domain\Admin;
use Modules\Admin\Http\Requests\ForgotPasswordRequest;
use Modules\Admin\Http\Requests\ResetPasswordRequest;

/**
 * Reset sandi mandiri admin (broker `admins`). Sandi baru mengganti hash, sehingga session admin lain
 * keluar lewat `auth.session`; `remember_token` diganti agar cookie "ingat saya" lama tidak berlaku.
 * 2FA tetap diminta saat login berikutnya.
 */
final class PasswordResetController
{
    public function request(Request $request): Response
    {
        return Inertia::render('admin/auth/ForgotPassword', ['status' => $request->session()->get('status')]);
    }

    public function email(ForgotPasswordRequest $request): RedirectResponse
    {
        Password::broker('admins')->sendResetLink($request->only('email'));

        // Pesan sama untuk semua hasil (terkirim, email tak dikenal, throttle) agar tidak membocorkan akun terdaftar.
        return back()->with('status', 'Jika email tersebut terdaftar, tautan untuk mengatur ulang kata sandi telah dikirim.');
    }

    public function edit(Request $request, string $token): Response
    {
        return Inertia::render('admin/auth/ResetPassword', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
            'passwordRules' => 'minlength: 12;',
        ]);
    }

    public function update(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::broker('admins')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Admin $admin, string $password) {
                $admin->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();

                event(new PasswordReset($admin));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => $status === Password::RESET_THROTTLED
                    ? 'Mohon tunggu sebelum mencoba lagi.'
                    : 'Tautan atur ulang kata sandi tidak valid atau sudah kedaluwarsa.',
            ]);
        }

        return redirect()->route('login')->with('status', 'Kata sandi berhasil diubah. Silakan masuk.');
    }
}
