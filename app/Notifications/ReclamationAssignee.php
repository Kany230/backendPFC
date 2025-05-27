<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Reclamation;

class ReclamationAssignee extends Notification
{
    use Queueable;

    protected $reclamation;

    public function __construct(Reclamation $reclamation)
    {
        $this->reclamation = $reclamation;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Nouvelle réclamation assignée')
            ->greeting('Nouvelle tâche')
            ->line('Une réclamation vous a été assignée.')
            ->line('Objet: ' . $this->reclamation->objet)
            ->line('Local: ' . $this->reclamation->local->nom)
            ->line('Priorité: ' . $this->reclamation->priorite)
            ->line('Description: ' . $this->reclamation->description)
            ->action('Voir la réclamation', url('/reclamations/' . $this->reclamation->id))
            ->line('Merci de traiter cette réclamation dans les meilleurs délais.');
    }

    public function toArray($notifiable)
    {
        return [
            'reclamation_id' => $this->reclamation->id,
            'objet' => $this->reclamation->objet,
            'local_nom' => $this->reclamation->local->nom,
            'priorite' => $this->reclamation->priorite,
            'utilisateur' => $this->reclamation->utilisateur->nom,
            'message' => 'Nouvelle réclamation assignée: ' . $this->reclamation->objet
        ];
    }
}