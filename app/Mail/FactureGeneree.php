<?php

namespace App\Mail;

use App\Models\Paiement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FactureGeneree extends Mailable
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
        return $this->subject('Nouvelle facture - CROUS')
            ->markdown('emails.facture-generee')
            ->with([
                'nom' => $this->paiement->utilisateur->nom,
                'montant' => number_format($this->paiement->montant, 0, ',', ' '),
                'dateEcheance' => $this->paiement->dateEcheance->format('d/m/Y'),
                'lienPaiement' => route('paiements.create'),
                'reference' => $this->paiement->reference
            ])
            ->attach(storage_path("app/factures/facture_{$this->paiement->id}.pdf"), [
                'as' => "facture_{$this->paiement->reference}.pdf",
                'mime' => 'application/pdf'
            ]);
    }
} 