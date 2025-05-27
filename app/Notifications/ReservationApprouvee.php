<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Reservation;

class ReservationApprouvee extends Notification
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
            ->subject('Réservation approuvée')
            ->line('Votre réservation a été approuvée.')
            ->line('Local : ' . $this->reservation->local->nom)
            ->line('Date : du ' . $this->reservation->date_debut . ' au ' . $this->reservation->date_fin)
            ->action('Voir la réservation', url('/reservations/' . $this->reservation->id))
            ->line('Merci.');
    }

    public function toArray($notifiable)
    {
        return [
            'reservation_id' => $this->reservation->id,
            'local_nom' => $this->reservation->local->nom,
            'statut' => 'Approuvée',
            'message' => 'Votre réservation a été approuvée.'
        ];
    }
}
