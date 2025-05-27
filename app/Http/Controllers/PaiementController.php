<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Paiement;
use App\Services\PdfService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PaiementController extends Controller
{

    public function index(Request $request)
    {
        $query = Paiement::with(['affectation', 'utilisateur']);

        if ($search = $request->input('search')) {
            $query->where('reference', 'like', "%{$search}%");
        }

        if ($affectationId = $request->input('affectation_id')) {
            $query->where('affectation_id', $affectationId);
        }

        if ($utilisateurId = $request->input('utilisateur_id')) {
            $query->where('utilisateur_id', $utilisateurId);
        }

        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }

        if ($dateDebut = $request->input('date_debut')) {
            $query->whereDate('datePaiement', '>=', $dateDebut);
        }

        if ($dateFin = $request->input('date_fin')) {
            $query->whereDate('datePaiement', '<=', $dateFin);
        }

        $orderBy = $request->input('orderBy', 'datePaiement');
        $orderDir = $request->input('orderDir', 'desc');
        $perPage = $request->input('perPage', 15);

        $paiements = $query->orderBy($orderBy, $orderDir)->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $paiements
        ]);
    }

    public function show($id){

        $paiement = Paiement::with(['affectation', 'utilisateur'])->find($id);

        if(!$paiement){
            return response()->json([
                'status' => 'error',
                'message' => 'Paiement non trouve'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => $paiement
        ]);
    }

    public function store(Request $request){

        $data = $request->validate([
            'id_affectation' => 'required|exists:affectation, id', 
            'montant' => 'required|numeric|mn:0', 
            'datePaiement' => 'required|date', 
            'dateEcheance' => 'nullable|date',
            'methode_paiement' => 'required|in:Wave,Orange Money,Espèces,Autre', 
            'reference' => 'required|string', 
            'remarques' => 'nullable|string'
        ]);

        $affectaion = Affectation::find($request->input('id_affectation'));

         /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();


        $data['id_utilisateur'] = $affectaion->utilisateur_id;
        $data['statut'] = 'Validé';
        $data['enregistre_par'] = $utilisateur->id;

        $paiement = Paiement::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Paiement enrgistre',
            'data' => $paiement
        ], 201);

    }

    public function update(Request $request, $id){

        $data = $request->validate([
            'montant' => 'required|numeric|mn:0', 
            'datePaiement' => 'required|date', 
            'dateEcheance' => 'nullable|date',
            'methode_paiement' => 'required|in:Wave,Orange Money,Espèces,Autre', 
            'reference' => 'required|string', 
            'remarques' => 'nullable|string'
        ]);

        $paiement = Paiement::find($id);
        
        if(!$paiement){
            return response()->json([
                'status' => 'error',
                'message' => 'Paiement non trouve'
            ], 404);
        }

        $paiement->update($data);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Paiement mis a jour',
            'data' => $paiement
        ]);

    }

    public function destroy($id) {

        $paiement = Paiement::find($id);

        if(!$paiement){
            return response()->json([
                'status' => 'error',
                'message' => 'Paiement non trouve'
            ], 404);
        }

        $paiement->delete();

        return response()->json([
            'status'=>'success',
            'message' => 'Paiement supprime'
        ]);
    }

    public function getEtatPaiements(Request $request)
    {
        $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'id_utilisateur' => 'nullable|exists:users,id'
        ]);

        $query = Paiement::with(['affectation.local.batiment', 'utilisateur']);

        if ($request->filled('date_debut')) {
            $query->whereDate('datePaiement', '>=', $request->input('date_debut'));
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('datePaiement', '<=', $request->input('date_fin'));
        }

        if ($request->filled('id_utilisateur')) {
            $query->where('id_utilisateur', $request->input('id_utilisateur'));
        }

        $paiements = $query->orderBy('datePaiement', 'desc')->get();

        $resume = [
            'total_paiements' => $paiements->count(),
            'montant_total' => $paiements->sum('montant'),
            'par_mode' => $paiements->groupBy('mode_paiement')->map(function ($group) {
                return [
                    'nombre' => $group->count(),
                    'montant' => $group->sum('montant')
                ];
            })
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'paiements' => $paiements,
                'resume' => $resume
            ]
        ]);
    }

    public function getPaiementsEnRetard()
    {
        $affectationsActives = Affectation::with(['local.batiment', 'utilisateur', 'paiements'])
            ->where('statut', 'Validé')
            ->get();

        $retards = collect();

        foreach ($affectationsActives as $affectation) {
            $dateDebut = Carbon::parse($affectation->date_debut);
            $maintenant = now();
            $moisEcoules = $dateDebut->diffInMonths($maintenant) + 1;
            $montantAttendu = $affectation->loyer_mensuel * $moisEcoules;
            $montantPaye = $affectation->paiements->sum('montant');

            if ($montantPaye < $montantAttendu) {
                $retards->push([
                    'affectation' => $affectation,
                    'montant_attendu' => $montantAttendu,
                    'montant_paye' => $montantPaye,
                    'montant_retard' => $montantAttendu - $montantPaye,
                    'mois_retard' => ceil(($montantAttendu - $montantPaye) / $affectation->loyer_mensuel)
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $retards->sortByDesc('montant_retard')->values()
        ]);
    }

    public function getPaiementsAffectation($affectationId)
    {
        $paiements = Paiement::where('id_affectation', $affectationId)
            ->orderBy('datePaiement', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $paiements
        ]);
    }

     // Valider un paiement
    public function validerPaiement($id, Request $request){

        $paiement = Paiement::find($id);

        if (!$paiement) {
            return response()->json([
                'status' => 'error',
                'message' => 'Paiement non trouvé'
            
            ], 404);  
        }

        $data = $request->validate([
            'methode_paiement' => 'required|in:Wave,Orange Money,Espèces,Autre',
            'reference' => 'nullable|string'
        ]);


        $paiement->valider($data['methode_paiement'], $validated['reference'] ?? null);

        return response()->json([
           'status' => 'success',
           'message' => 'Paiement validé avec succès',
           'data' => $paiement
        ]);
    }

    
    // Annuler un paiement
    public function annulerPaiement($id, Request $request)
{
        $paiement = Paiement::find($id);

        if (!$paiement) {
            return response()->json([
                'status' => 'error',
                'message' => 'Paiement non trouvé'
            ], 404);
        }

        $validated = $request->validate([
            'raison' => 'nullable|string'
        ]);

        $paiement->annuler($validated['raison']);

        return response()->json([
            'status' => 'success',
            'message' => 'Paiement annulé avec succès',
            'data' => $paiement
        ]);
    }

    
    // Générer une quittance de paiement au format PDF
   public function genererQuittance($id){
        $paiement = Paiement::find($id);

        if (!$paiement) {
            return response()->json([
                'status' => 'error',
                'message' => 'Paiement non trouvé'
            ], 404);
        }

        try {
            $quittance = $paiement->genererQuittance();
            return response()->json([
                'status' => 'success',
                'message' => 'Quittance générée avec succès',
                'data' => $quittance
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

}





