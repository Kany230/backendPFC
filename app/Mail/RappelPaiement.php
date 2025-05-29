<?php

namespace App\Mail;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RappelPaiement extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $montantDu;
    public $dateEcheance;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, float $montantDu, Carbon $dateEcheance)
    {
        $this->user = $user;
        $this->montantDu = $montantDu;
        $this->dateEcheance = $dateEcheance;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Rappel de paiement - CROUS')
            ->markdown('emails.rappel-paiement')
            ->with([
                'nom' => $this->user->nom,
                'montant' => number_format($this->montantDu, 0, ',', ' '),
                'dateEcheance' => $this->dateEcheance->format('d/m/Y'),
                'lienPaiement' => route('paiements.create')
            ]);
    }
} 