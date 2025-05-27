<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Paiement;

class FactureCreee extends Notification
{
    use Queueable;

    protected $paiement;

    public function __construct(Paiement $paiement)
    {
        $this->paiement = $paiement;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nouvelle facture créée')
            ->line('Une nouvelle facture a été créée pour votre contrat.')
            ->line('Montant: ' . number_format($this->paiement->montant, 2) . ' €')
            ->line('Date d\'échéance: ' . $this->paiement->date_echeance->format('d/m/Y'))
            ->action('Voir la facture', url('/paiements/' . $this->paiement->id))
            ->line('Merci de procéder au paiement avant la date d\'échéance.');
    }

    public function toArray($notifiable)
    {
        return [
            'paiement_id' => $this->paiement->id,
            'contrat_reference' => $this->paiement->contrat->reference,
            'montant' => $this->paiement->montant,
            'date_echeance' => $this->paiement->date_echeance,
            'message' => 'Nouvelle facture créée'
        ];
    }
}