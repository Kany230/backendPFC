<?php

namespace App\Services;

use App\Models\Contrat;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;
use PDF;

class ContratService
{
    private $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Créer un nouveau contrat
     */
    public function creerContrat(array $data): Contrat
    {
        $contrat = new Contrat();
        $contrat->id_affectation = $data['id_affectation'];
        $contrat->reference = $this->genererReference();
        $contrat->dateDebut = $data['dateDebut'];
        $contrat->dateFin = $data['dateFin'];
        $contrat->montant = $data['montant'];
        $contrat->frequence_paiement = $data['frequence_paiement'];
        $contrat->type = $data['type'];
        $contrat->statut = 'Actif';
        $contrat->save();

        // Générer le PDF du contrat
        $this->genererContratPDF($contrat);

        // Envoyer le contrat par email
        $this->envoyerContratParEmail($contrat);

        return $contrat;
    }

    /**
     * Renouveler un contrat
     */
    public function renouvelerContrat(Contrat $contrat, Carbon $newDateFin): Contrat
    {
        $contrat->dateFin = $newDateFin;
        $contrat->save();

        // Générer le nouveau PDF
        $this->genererContratPDF($contrat);

        // Envoyer le contrat renouvelé par email
        $this->envoyerContratParEmail($contrat, true);

        return $contrat;
    }

    /**
     * Résilier un contrat
     */
    public function resilierContrat(Contrat $contrat, string $raison): Contrat
    {
        $contrat->statut = 'Résilié';
        $contrat->raison_resiliation = $raison;
        $contrat->date_resiliation = now();
        $contrat->save();

        return $contrat;
    }

    /**
     * Générer une référence unique pour le contrat
     */
    private function genererReference(): string
    {
        $prefix = 'CROUS-';
        $year = date('Y');
        $random = strtoupper(Str::random(6));
        return "{$prefix}{$year}-{$random}";
    }

    /**
     * Générer le PDF du contrat
     */
    private function genererContratPDF(Contrat $contrat): void
    {
        $pdf = PDF::loadView('pdfs.contrat', [
            'contrat' => $contrat,
            'utilisateur' => $contrat->affectation->utilisateur,
            'pavillon' => $contrat->affectation->chambre->pavillon,
            'chambre' => $contrat->affectation->chambre
        ]);

        $filename = "contrat_{$contrat->reference}.pdf";
        $pdf->save(storage_path("app/contrats/{$filename}"));
    }

    /**
     * Envoyer le contrat par email
     */
    private function envoyerContratParEmail(Contrat $contrat, bool $isRenouvellement = false): void
    {
        $utilisateur = $contrat->affectation->utilisateur;
        $sujet = $isRenouvellement ? 'Renouvellement de contrat' : 'Nouveau contrat';
        
        $this->emailService->envoyerContrat($utilisateur, $contrat, $sujet);
    }
} 