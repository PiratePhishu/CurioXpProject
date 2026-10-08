<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class TeacherResetPasswordNotification extends ResetPassword
{
    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $url = route('teacher.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Wachtwoord resetten - Curio XP-Tracker')
            ->line('Je ontvangt deze e-mail omdat we een verzoek hebben gekregen om je docentwachtwoord te resetten.')
            ->action('Wachtwoord resetten', $url)
            ->line('Deze resetlink verloopt over '.config('auth.passwords.users.expire').' minuten.')
            ->line('Als je geen wachtwoordreset hebt aangevraagd, hoef je niets te doen.');
    }
}
