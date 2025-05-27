<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NouveauMotDePasse extends Mailable
{
    use Queueable, SerializesModels;

    public $motDePasse;

    public function __construct($motDePasse)
    {
        $this->motDePasse = $motDePasse;
    }

    public function build()
    {
        return $this->subject('Votre nouveau mot de passe')
                    ->view('emails.nouveau-mot-de-passe')
                    ->with(['motDePasse' => $this->motDePasse]);
    }
}
