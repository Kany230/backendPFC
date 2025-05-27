<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\DemandeAffectation;

class DemandeAffectationApprouvee extends Notification
{
    use Queueable;

    protected $demandeAffectation;

    public function __construct(DemandeAffectation $demandeAffectation)
    {
        $this->demandeAffectation = $demandeAffectation;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Demande d\'affectation approuvée')
            ->greeting('Bonne nouvelle !')
            ->line('Votre demande d\'affectation a été approuvée.')
            ->line('Local: ' . $this->demandeAffectation->local->nom)
            ->line('Type d\'occupation: ' . $this->demandeAffectation->type_occupation)
            ->action('Voir les détails', url('/demandes-affectation/' . $this->demandeAffectation->id))
            ->line('Vous recevrez prochainement les détails de votre contrat.');
    }

    public function toArray($notifiable)
    {
        return [
            'demande_id' => $this->demandeAffectation->id,
            'local_nom' => $this->demandeAffectation->local->nom,
            'type_occupation' => $this->demandeAffectation->type_occupation,
            'message' => 'Votre demande d\'affectation a été approuvée'
        ];
    }
}