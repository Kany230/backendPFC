<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Affectation;

class AffectationResiliee extends Notification
{
    use Queueable;

    protected $affectation;
    protected $raison;

    public function __construct(Affectation $affectation, $raison = null)
    {
        $this->affectation = $affectation;
        $this->raison = $raison;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Affectation résiliée')
            ->line('Votre affectation a été résiliée.');
            
        if ($this->raison) {
            $message->line('Raison: ' . $this->raison);
        }
        
        return $message->action('Voir les détails', url('/affectations/' . $this->affectation->id));
    }

    public function toArray($notifiable)
    {
        return [
            'affectation_id' => $this->affectation->id,
            'message' => 'Votre affectation a été résiliée',
            'raison' => $this->raison
        ];
    }
}