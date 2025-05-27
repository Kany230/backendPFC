<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    public function index(){
        
        $contrats = Contrat::with('affectation.utilisateur', 'paiements', 'alertes')->get();

        return response()->json([
            'status' => 'success',
            'data' => $contrats
        ]);
    }

    public function show($id){

        $contrat = Contrat::with('affectation.utilisateur', 'paiements', 'alertes')->findOrFail($id);

        if(!$contrat){
            return response()->json([
                'statut' => 'error',
                'message' => 'contrat non trouve'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $contrat
        ]);

    }

    public function store(Request $request){

        $data = $request->validate([
            'id_affectation' => 'required|exists:affectations,id',
            'reference' => 'required|string|unique:contrats,reference',
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date|after:dateDebut',
            'montant' => 'required|numeric',
            'frequence_paiement' => 'required|in:Mensuel,Trimestriel,Semestriel,Annuel',
            'type' => 'required|in:Location,Sous-location,Convention',
            'statut' => 'required|in:Actif,Expiré,Résilié'
        ]);

        $contrat = Contrat::create($data);

        $contrat->creerAlertesEcheance();

        return response()->json([
            'status' => 'success',
            'data' => $contrat
        ], 201);
    }

    public function update(Request $request, $id){

        $contrat = Contrat::findOrFail($id);

        $data = $request->validate([
            'dateDebut' => 'nullable|date',
            'dateFin' => 'nullable|date|after:dateDebut',
            'montant' => 'nullable|numeric',
            'frequence_paiement' => 'nullable|in:Mensuel,Trimestriel,Semestriel,Annuel',
            'type' => 'nullable|in:Location,Sous-location,Convention',
            'statut' => 'nullable|in:Actif,Expiré,Résilié'
        ]);

        $contrat->update($data);
        $contrat->creerAlertesEcheance();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function destroy($id){

        $contrat = Contrat::findOrFail($id);

        if(!$contrat){
            return response()->json([
                'status' => 'error',
                'message' => 'Contrat non trouve'
            ], 404);
        }

        $contrat->delete();

        return response()->json([
            'status' =>'success',
            'message' => 'Contrat supprimer'
        ]);
    }

    public function renouveler(Request $request, $id){

        $request->validate([
            'newDateFin' => 'required|date|after:today'
        ]);
        $contrat = Contrat::findOrFail($id);

        $contrat->renouveler($request->newDateFin);

        return response()->json([
            'status' => 'success',
            'message' => 'Contrat renouvele',
            'data' => $contrat
        ]);
    }

    public function resilier(Request $request, $id){

        $contrat = Contrat::findOrFail($id);
        
        $contrat->resilier($request->input('raison'));

        return response()->json([
            'status' => 'success',
            'message' => 'Contrat renouvele',
            'data' => $contrat
        ]);
    }

    public function genererFacture(Request $request, $id){

        $request->validate([
            'montant' => 'required|numeric',
            'dateEcheance' => 'required|date|after:today'
        ]);
        $contrat = Contrat::findOrFail($id);
        
        $paiement = $contrat->genererFacture($request->montant, $request->dateEcheance);

        return response()->json([
            'status' => 'success',
            'message' => 'Facture genere',
            'data' => $paiement
        ]);
    }

        public function generePDF($id){

        $contrat = Contrat::findOrFail($id);
        
        $pdf = $contrat->genererPDF();

        return $pdf->download("contrat_{$contrat->reference}.pdf");
    }
}
