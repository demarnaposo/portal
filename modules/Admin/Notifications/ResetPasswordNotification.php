<?php

namespace Modules\Admin\Notifications;

use App\Notifications\ActionMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Admin\Domain\Admin;

final class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly string $token) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(Admin $notifiable): MailMessage
    {
        return ActionMail::make(
            subject: 'Atur ulang kata sandi panel admin',
            name: $notifiable->name,
            intro: ['Kami menerima permintaan untuk mengatur ulang kata sandi akun panel admin Anda.'],
            actionText: 'Atur ulang kata sandi',
            actionUrl: route('admin.password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]),
            outro: [
                'Tautan ini berlaku selama '.config('auth.passwords.admins.expire').' menit. Setelah kata sandi diubah, '
                    .'autentikasi dua faktor tetap diminta saat masuk.',
                'Jika Anda tidak meminta pengaturan ulang, abaikan email ini dan beri tahu pemilik akun; kata sandi Anda tidak berubah.',
            ],
        );
    }
}
