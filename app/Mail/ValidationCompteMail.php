<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ValidationCompteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $statut;

    public function __construct($statut)
    {
        $this->statut = $statut;
    }

    public function build()
    {
        return $this->subject('Validation de votre compte')
                    ->view('emails.validation_compte');
    }
}
