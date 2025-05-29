<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Contrat;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContratGenere extends Mailable
{
    use Queueable, SerializesModels;

    public $utilisateur;
    public $contrat;
    public $sujet;

    /**
     * Create a new message instance.
     */
    public function __construct(User $utilisateur, Contrat $contrat, string $sujet)
    {
        $this->utilisateur = $utilisateur;
        $this->contrat = $contrat;
        $this->sujet = $sujet;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->sujet . ' - CROUS')
            ->markdown('emails.contrat-genere')
            ->with([
                'nom' => $this->utilisateur->nom,
                'reference' => $this->contrat->reference,
                'dateDebut' => $this->contrat->dateDebut->format('d/m/Y'),
                'dateFin' => $this->contrat->dateFin->format('d/m/Y'),
                'montant' => number_format($this->contrat->montant, 0, ',', ' '),
                'pavillon' => $this->contrat->affectation->chambre->pavillon->nom,
                'chambre' => $this->contrat->affectation->chambre->numero
            ])
            ->attach(storage_path("app/contrats/contrat_{$this->contrat->reference}.pdf"), [
                'as' => "contrat_{$this->contrat->reference}.pdf",
                'mime' => 'application/pdf'
            ]);
    }
} 