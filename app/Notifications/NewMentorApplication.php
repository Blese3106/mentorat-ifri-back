<?php

namespace App\Notifications;

use App\Models\Mentor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewMentorApplication extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Mentor $mentor)
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
            ->subject('🎓 Nouvelle candidature mentor — IFRI Connect')
            ->greeting('Bonjour Admin,')
            ->line('Une nouvelle candidature mentor vient d\'être soumise sur IFRI Connect.')
            ->line('**Candidat :** ' . $this->mentor->firstname . ' ' . $this->mentor->lastname)
            ->line('**Email :** ' . $this->mentor->email)
            ->line('**Filière :** ' . ($this->mentor->filiere ?? 'Non précisée'))
            ->line('**Expérience :** ' . $this->mentor->experience . ' an(s)')
            ->line('**Entreprise :** ' . ($this->mentor->company ?? '—'))
            ->line('**Poste :** ' . ($this->mentor->poste ?? '—'))
            ->line('**Référence :** ' . ($this->mentor->email_contact ?? 'Non fournie'))
            ->action('Voir la candidature', env('FRONTEND_URL', 'http://localhost:3000') . '/pages/admin/validation_mentor')
            ->line('Merci de traiter cette candidature dans les 48–72h.');
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
