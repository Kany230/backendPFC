<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Paiement;

class PaiementValide extends Notification
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
            ->subject('Paiement validé')
            ->greeting('Confirmation de paiement')
            ->line('Votre paiement a été validé avec succès.')
            ->line('Montant: ' . number_format($this->paiement->montant, 2) . ' €')
            ->line('Méthode: ' . $this->paiement->methode_paiement)
            ->line('Date de paiement: ' . $this->paiement->date_paiement->format('d/m/Y'))
            ->when($this->paiement->reference, function ($message) {
                return $message->line('Référence: ' . $this->paiement->reference);
            })
            ->action('Télécharger la quittance', url('/paiements/' . $this->paiement->id . '/quittance'))
            ->line('Merci pour votre paiement !')
            ->salutation('Cordialement, L\'équipe de gestion des locaux');
    }

    public function toArray($notifiable)
    {
        return [
            'paiement_id' => $this->paiement->id,
            'contrat_reference' => $this->paiement->contrat->reference,
            'montant' => $this->paiement->montant,
            'methode_paiement' => $this->paiement->methode_paiement,
            'date_paiement' => $this->paiement->date_paiement,
            'reference' => $this->paiement->reference,
            'message' => 'Paiement validé avec succès'
        ];
    }
}