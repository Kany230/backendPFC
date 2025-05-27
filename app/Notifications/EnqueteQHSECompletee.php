<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\EnqueteQHSE;

class EnqueteQHSECompletee extends Notification
{
    use Queueable;

    protected $enqueteQHSE;

    public function __construct(EnqueteQHSE $enqueteQHSE)
    {
        $this->enqueteQHSE = $enqueteQHSE;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $conforme = $this->enqueteQHSE->conforme ? 'Conforme' : 'Non conforme';
        
        $message = (new MailMessage)
            ->subject('Enquête QHSE terminée')
            ->line('Une enquête QHSE a été terminée.')
            ->line('Local: ' . $this->enqueteQHSE->local->nom)
            ->line('Agent QHSE: ' . $this->enqueteQHSE->agentQHSE->nom)
            ->line('Résultat: ' . $conforme)
            ->line('Date de fin: ' . $this->enqueteQHSE->date_fin->format('d/m/Y'));
            
        if ($this->enqueteQHSE->conclusion) {
            $message->line('Conclusion: ' . $this->enqueteQHSE->conclusion);
        }
        
        return $message
            ->action('Voir le rapport', url('/enquetes-qhse/' . $this->enqueteQHSE->id))
            ->line('Vous pouvez maintenant procéder à la validation administrative de la demande d\'affectation.');
    }

    public function toArray($notifiable)
    {
        return [
            'enquete_id' => $this->enqueteQHSE->id,
            'local_nom' => $this->enqueteQHSE->local->nom,
            'agent_qhse' => $this->enqueteQHSE->agentQHSE->nom,
            'conforme' => $this->enqueteQHSE->conforme,
            'date_fin' => $this->enqueteQHSE->date_fin,
            'demande_affectation_id' => $this->enqueteQHSE->id_demande_affectation,
            'message' => 'Enquête QHSE terminée - ' . ($this->enqueteQHSE->conforme ? 'Conforme' : 'Non conforme')
        ];
    }
}