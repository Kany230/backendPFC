<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Reservation;

class ReservationAnnulee extends Notification
{
    use Queueable;

    protected $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Réservation annulée')
            ->line('Votre réservation a été annulée.')
            ->line('Local : ' . $this->reservation->local->nom)
            ->line('Raison : ' . $this->reservation->remarques)
            ->action('Voir la réservation', url('/reservations/' . $this->reservation->id))
            ->line('Merci de votre compréhension.');
    }

    public function toArray($notifiable)
    {
        return [
            'reservation_id' => $this->reservation->id,
            'local_nom' => $this->reservation->local->nom,
            'statut' => 'Annulée',
            'message' => 'Votre réservation a été annulée.'
        ];
    }
}
