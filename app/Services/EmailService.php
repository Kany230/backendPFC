<?php

namespace App\Services;

use App\Models\User;
use App\Models\Paiement;
use Illuminate\Support\Facades\Mail;
use App\Mail\RappelPaiement;
use App\Mail\PaiementValide;
use App\Mail\FactureGeneree;
use Carbon\Carbon;

class EmailService
{
    /**
     * Envoyer un email de rappel de paiement
     */
    public function envoyerRappelPaiement(User $user, float $montantDu, Carbon $dateEcheance)
    {
        Mail::to($user->email)->send(new RappelPaiement($user, $montantDu, $dateEcheance));
    }

    /**
     * Envoyer un email de confirmation de paiement
     */
    public function envoyerConfirmationPaiement(Paiement $paiement)
    {
        Mail::to($paiement->utilisateur->email)->send(new PaiementValide($paiement));
    }

    /**
     * Envoyer un email avec la facture générée
     */
    public function envoyerFacture(Paiement $paiement)
    {
        Mail::to($paiement->utilisateur->email)->send(new FactureGeneree($paiement));
    }

    /**
     * Envoyer un rappel mensuel pour les paiements en retard
     */
    public function envoyerRappelMensuel(User $user, array $paiementsEnRetard)
    {
        $montantTotal = collect($paiementsEnRetard)->sum('montant_retard');
        $message = "Rappel: Vous avez {$montantTotal} FCFA de paiements en retard.";
        
        Mail::to($user->email)->send(new RappelPaiement($user, $montantTotal, now()));
    }

    /**
     * Envoyer un rappel pour le paiement du mois
     */
    public function envoyerRappelDebutMois(User $user, float $montantMensuel)
    {
        $message = "Rappel: Votre paiement mensuel de {$montantMensuel} FCFA est dû.";
        
        Mail::to($user->email)->send(new RappelPaiement($user, $montantMensuel, now()->endOfMonth()));
    }
} 