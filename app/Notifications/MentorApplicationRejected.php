<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MentorApplicationRejected extends Notification
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
            ->subject('❌ Candidature non retenue — IFRI Connect')
            ->greeting('Bonjour ' . $notifiable->firstname . ',')
            ->line('Nous avons examiné votre candidature en tant que mentor sur IFRI Connect.')
            ->line('Après étude de votre dossier, nous ne pouvons pas y donner suite pour le moment.');
 
        if ($this->reason) {
            $mail->line('**Motif :** ' . $this->reason);
        }
 
        return $mail
            ->line('Vous pouvez soumettre une nouvelle candidature ultérieurement.')
            ->line('Merci de votre intérêt pour IFRI Connect.');
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
