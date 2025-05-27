<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\DemandeAffectation;

class DemandeAffectationRejetee extends Notification
{
    use Queueable;

    protected $demandeAffectation;
    protected $raison;

    public function __construct(DemandeAffectation $demandeAffectation, $raison)
    {
        $this->demandeAffectation = $demandeAffectation;
        $this->raison = $raison;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Demande d\'affectation rejetée')
            ->line('Nous sommes désolés de vous informer que votre demande d\'affectation a été rejetée.')
            ->line('Local demandé: ' . $this->demandeAffectation->local->nom)
            ->line('Raison du rejet: ' . $this->raison)
            ->action('Voir les détails', url('/demandes-affectation/' . $this->demandeAffectation->id))
            ->line('Vous pouvez soumettre une nouvelle demande en tenant compte des remarques.')
            ->salutation('Cordialement, L\'équipe de gestion des locaux');
    }

    public function toArray($notifiable)
    {
        return [
            'demande_id' => $this->demandeAffectation->id,
            'local_nom' => $this->demandeAffectation->local->nom,
            'raison' => $this->raison,
            'message' => 'Votre demande d\'affectation a été rejetée'
        ];
    }
}