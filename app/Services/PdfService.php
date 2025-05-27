<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Contrat;

class PdfService
{
    /**
     * Génère un PDF pour un contrat donné.
     *
     * @param  Contrat  $contrat
     * @return \Barryvdh\DomPDF\PDF
     */
    public function genererContratPDF($contrat)
{
    return Pdf::loadView('pdfs.contrat', [
        'contrat' => $contrat
    ]);
}
    public function genererQuittancePDF($quittance)
    {
        return Pdf::loadView('pdfs.quittance', [
            'quittance' => $quittance
        ])->stream("quittance_{$quittance->id}.pdf");
    }

public function genererRapportEnqueteQHSE($rapport)
{
    return Pdf::loadView('pdfs.rapport_qhse', [
        'rapport' => $rapport
    ])->stream("rapport_qhse_{$rapport->id}.pdf");
}

}
