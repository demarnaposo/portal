<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;

/** Email transaksional berbahasa Indonesia dengan satu tombol aksi (template `resources/views/mail`, dipakai modul Identity & Admin). */
final class ActionMail
{
    /**
     * @param  list<string>  $intro
     * @param  list<string>  $outro
     */
    public static function make(string $subject, string $name, array $intro, string $actionText, string $actionUrl, array $outro): MailMessage
    {
        return (new MailMessage)
            ->subject($subject)
            ->view(['mail.action', 'mail.action-text'], [
                'subject' => $subject,
                'name' => $name,
                'intro' => $intro,
                'actionText' => $actionText,
                'actionUrl' => $actionUrl,
                'outro' => $outro,
                'appName' => config('mail.from.name', 'Sertifika'),
            ]);
    }
}
