<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentorApplicationApproved extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('✅ Candidature approuvée — IFRI Connect')
            ->greeting('Félicitations ' . $notifiable->firstname . ' !')
            ->line('Votre candidature en tant que mentor sur **IFRI Connect** a été approuvée.')
            ->line('Vous pouvez maintenant vous connecter et commencer à accompagner des étudiants.')
            ->action('Se connecter', env('FRONTEND_URL', 'http://localhost:3000') . '/auth/connexion')
            ->line('Bienvenue dans la communauté IFRI Connect !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
