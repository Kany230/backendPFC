<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Reclamation;

class ReclamationResolue extends Notification
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
            ->subject('Réclamation résolue')
            ->greeting('Bonne nouvelle !')
            ->line('Votre réclamation a été résolue.')
            ->line('Objet: ' . $this->reclamation->objet)
            ->line('Local: ' . $this->reclamation->local->nom)
            ->line('Solution appliquée: ' . $this->reclamation->solution)
            ->line('Date de résolution: ' . $this->reclamation->date_resolution->format('d/m/Y'))
            ->action('Évaluer notre service', url('/reclamations/' . $this->reclamation->id . '/satisfaction'))
            ->line('Nous espérons que cette solution répond à vos attentes. N\'hésitez pas à nous faire part de votre satisfaction.');
    }

    public function toArray($notifiable)
    {
        return [
            'reclamation_id' => $this->reclamation->id,
            'objet' => $this->reclamation->objet,
            'local_nom' => $this->reclamation->local->nom,
            'solution' => $this->reclamation->solution,
            'date_resolution' => $this->reclamation->date_resolution,
            'message' => 'Réclamation résolue: ' . $this->reclamation->objet
        ];
    }
}