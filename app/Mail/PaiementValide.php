<?php

namespace App\Mail;

use App\Models\Paiement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaiementValide extends Mailable
{
    use Queueable, SerializesModels;

    public $paiement;

    /**
     * Create a new message instance.
     */
    public function __construct(Paiement $paiement)
    {
        $this->paiement = $paiement;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Confirmation de paiement - CROUS')
            ->markdown('emails.paiement-valide')
            ->with([
                'nom' => $this->paiement->utilisateur->nom,
                'montant' => number_format($this->paiement->montant, 0, ',', ' '),
                'datePaiement' => $this->paiement->datePaiement->format('d/m/Y'),
                'methodePaiement' => $this->paiement->methode_paiement,
                'reference' => $this->paiement->reference,
                'lienQuittance' => route('paiements.quittance', ['id' => $this->paiement->id])
            ])
            ->attach(storage_path("app/quittances/quittance_{$this->paiement->id}.pdf"), [
                'as' => "quittance_paiement_{$this->paiement->id}.pdf",
                'mime' => 'application/pdf'
            ]);
    }
} 