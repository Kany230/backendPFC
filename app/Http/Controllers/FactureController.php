<?php

namespace App\Http\Controllers;

use App\Models\Paiement;
use Illuminate\Http\Request;
use PDF;

class FactureController extends Controller
{
    public function genererFacture(Paiement $paiement)
    {
        $etudiant = $paiement->etudiant;
        $chambre = $paiement->chambre;
        
        $data = [
            'paiement' => $paiement,
            'etudiant' => $etudiant,
            'chambre' => $chambre,
            'date' => now()->format('d/m/Y'),
            'numero_facture' => 'FACT-' . str_pad($paiement->id, 6, '0', STR_PAD_LEFT)
        ];

        $pdf = PDF::loadView('factures.template', $data);
        
        return $pdf->download('facture_' . $data['numero_facture'] . '.pdf');
    }
} 