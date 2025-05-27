<?php

namespace App\Http\Controllers;

use App\Models\DemandeAffectation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemandeAffectationController extends Controller
{
public function index(Request $request)
{
    $query = DemandeAffectation::query();

    if ($request->has('statut')) {
        $query->where('statut', $request->input('statut'));
    }

    $demandes = $query->with(['utilisateur', 'local'])->get();

    return response()->json([
        'status' => 'success',
        'data' => $demandes
    ]);
}


    public function show($id){

        $demande = DemandeAffectation::with(['utilisateur', 'local'])->findOrFail($id);

        if(!$demande){
            return response()->json([
                'status' => 'error',
                'message' => 'Demande non trouve'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $demande
        ]);
    }

    public function store(Request $request){

        /** @var \App\Models\User$user **/
        $user = Auth::user();

        $validated = $request->validate([
            'id_local' => 'required|exists:locals,id',
            'typeOccupation' => 'required|in:Temporarire,Permanent,Saisoniere',
        ]);

        $demande = DemandeAffectation::create([
            'id_local' => $validated['id_local'],
            'id_utilisateur' => $user->id,
            'dateCreation' => now(),
            'statut' => 'En attente',
            'typeOccupation' => $validated['typeOccupation'],
            'avisqhse' => false,
            'validationGestionnaire' => false,
        ]);

        return response()->json([
            'status' => 'success',
            'demande' => $demande
        ], 201);
    }

     public function approuver(Request $request, $id)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        $demande = DemandeAffectation::findOrFail($id);

        try {
            $affectation = $demande->approuver($request->date_debut, $request->date_fin);
            return response()->json([
                'message' => 'Demande approuvée.', 
                'affectation' => $affectation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    
    public function rejeter(Request $request, $id)
    {
        $request->validate([
            'raison' => 'required|string'
        ]);

        $demande = DemandeAffectation::findOrFail($id);
        $demande->rejeter($request->raison);

        return response()->json([
            'message' => 'Demande rejetée.'
        ]);
    }

    public function getDemandesUtilisateur($id){

        $demandes = DemandeAffectation::where('id_utilisateur', $id)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $demandes
        ]);
    }

    public function getStatistiques()
{
    $statistiques = [
        'total' => DemandeAffectation::count(),
        'en_attente' => DemandeAffectation::where('statut', 'En attente')->count(),
        'en_cours' => DemandeAffectation::where('statut', 'En cours')->count(),
        'approuvees' => DemandeAffectation::where('statut', 'Approuvee')->count(),
        'refusees' => DemandeAffectation::where('statut', 'Refusee')->count(),
        'ce_mois' => DemandeAffectation::whereMonth('dateCreation', now()->month)
            ->whereYear('dateCreation', now()->year)
            ->count()
    ];
    
    return response()->json([
        'status' => 'success',
        'data' => $statistiques
    ]);
}


}
