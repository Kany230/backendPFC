<?php

namespace App\Mail;

use App\Models\DemandeAffectation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AffectationAcceptee extends Mailable
{
    use Queueable, SerializesModels;

    public $demande;

    public function __construct(DemandeAffectation $demande)
    {
        $this->demande = $demande;
    }

    public function build()
    {
        return $this->subject('Votre demande d\'affectation a été acceptée')
                    ->markdown('emails.affectations.acceptee');
    }
} 