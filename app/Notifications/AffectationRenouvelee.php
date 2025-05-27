<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Affectation;

class AffectationRenouvelee extends Notification
{
    use Queueable;

    protected $affectation;

    public function __construct(Affectation $affectation)
    {
        $this->affectation = $affectation;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Affectation renouvelée')
            ->line('Votre affectation a été renouvelée.')
            ->line('Nouvelle date de fin: ' . $this->affectation->dateFin)
            ->action('Voir les détails', url('/affectations/' . $this->affectation->id));
    }

    public function toArray($notifiable)
    {
        return [
            'affectation_id' => $this->affectation->id,
            'message' => 'Votre affectation a été renouvelée',
            'nouvelle_date_fin' => $this->affectation->dateFin
        ];
    }
}