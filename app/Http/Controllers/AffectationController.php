<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\DemandeAffectation;
use App\Models\Local;
use App\Models\User;
use FontLib\Table\Type\loca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use function Illuminate\Log\log;

class AffectationController extends Controller
{

    public function index(Request $request) {
        
       $query = Affectation::with(['utilisateur', 'local']);
        // Filtres
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%$search%")
                   ->orWhereHas('utilisateur', function ($q2) use ($search) {
                $q2->where('nom', 'like', "%$search%")
                   ->orWhere('prenom', 'like', "%$search%");
              })
                ->orWhereHas('local', function ($q3) use ($search) {
                $q3->where('nom', 'like', "%$search%");
              });
           
            });
        
        }

        if ($utilisateurId = $request->input('id_utilisateur')) {
            $query->where('id_utilisateur', $utilisateurId);
        }

        if ($localId = $request->input('id_local')) {
            $query->where('id_local', $localId);
        }

        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }

        // Tri
        $orderBy = $request->input('orderBy', 'dateDebut');
        $orderDir = $request->input('orderDir', 'desc');

        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = $request->input('perPage', 15);
        $affectations = $query->with(['utilisateur', 'local'])->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $affectations
        ]);
    }

    public function show($id){

        $affectation = Affectation::with(['utilisateur', 'local'])->find($id);

        if(!$affectation){
            return response()->json([
                'status' => 'error',
                'message' => 'Affectation non trouvee'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => $affectation
        ]);
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'id_demande_affectation' => 'required|exists:demande_affectations,id',
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date|after:dateDebut',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $demande = DemandeAffectation::findOrFail($request->id_demande_affectation);

        if ($demande->statut !== 'Approuvee') {
            return response()->json(['error' => 'La demande doit etre approuvée avant affectation.'], 400);
        }

        $affectation = Affectation::create([
            'id_demande_affectation' => $demande->id,
            'id_local' => $demande->id_local,
            'id_utilisateur' => $demande->id_utilisateur,
            'type' => $demande->typeOccupation,
            'dateDebut' => $request->dateDebut,
            'dateFin' => $request->dateFin,
            'statut' => 'Active'
        ]);

        $demande->local->update(['disponible' => false]);

        return response()->json($affectation, 201);
    }


    public function update(Request $request, $id){

        $affectation = Affectation::find($id);

        if(!$affectation){
            return response()->json([
                'status' => 'error',
                'message' => 'Affectation non trouve'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'dateDebut' => 'nullable|date',
            'dateFin' => 'nullable|date|after:dateDebut',
            'statut' => 'nullable|in:Active,Expire,Resiliee'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $affectation->update($request->only(['dateDebut', 'dateFin', 'statut']));

        return response()->json([
            'status' => 'success',
            'message' => 'Affectation mis a jour',
            'affectation' => $affectation
        ]);
    }

    public function destroy($id){

        $affectation = Affectation::with('local')->find($id);

        if(!$affectation){
            return response()->json([
                'status' => 'error',
                'message' => 'Affectation non trouve'
            ], 404);
        }

        $affectation->local->update(['disponible' => true]);

        $affectation->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Affectation supprime'
        ]);
    }

    public function resilier($id, Request $request){

        $affectation = Affectation::findOrFail($id);

        try {

            $affectation->resilier($request->input('raison'));
            return response()->json(['message' => 'Affectation résiliée avec succès.']);

        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 400);

        }
    }

    public function renouveler($id, Request $request){

        $validator = Validator::make($request->all(), [
            'nouvelle_date_fin' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $affectation = Affectation::findOrFail($id);

        try {
            $affectation->renouveler($request->input('nouvelle_date_fin'));
            return response()->json(['message' => 'Affectation renouvelée avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function approuveDemande(Request $request, $id){

        $request->validate([
            'id_local' => 'required|exists:locaux,id',
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date|after:date_debut',
        ]);

        $demande = DemandeAffectation::find($id);

        if(!$demande){
            return response()->json([
                'status' => 'error',
                'message' => 'Demande non trouve'
            ], 404);
        }

        if($demande->statut !== 'en attente'){
            return response()->json([
                'status' => 'error',
                'message' => 'Demande a deja ete traite'
            ], 400);
        }

        $local = Local::find($request->input('id_local'));

        $disponible = $local->verifierDisponibilite(
            $request->input('dateDebut'),
            $request->input('dateFin')
        );

        if(!$disponible){
            return response()->json([
                'status' => 'error',
                'message' => 'Ce local n\'est pas $disponible'
            ], 400);
        }

        $affectation = Affectation::create([
            'id_utilisateur' => $demande->id_utilisateur,
            'id_local' => $request->input('id_local'),
            'dateDebut' => $request->input('date_debut'),
            'dateFin' => $request->input('date_fin'),
            'statut' => 'Active'
        ]);
        
        // Mettre à jour la demande
        $demande->update([
            'statut' => 'approuvee',
            'id_affectation' => $affectation->id,
            'traite_par' => Auth::id(),
            'traite_le' => now()
        ]);
        
        // Mettre à jour le statut du local
        $local->update(['disponible' => false]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Demande approuvée et affectation créée avec succès',
            'data' => $affectation
        ], 201);
    }

    public function getAffectationsUtilisateur($userId)
    {
        $affectations = Affectation::with(['local.batiment'])
            ->where('id_utilisateur', $userId)
            ->orderBy('dateDebut', 'desc')
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $affectations
        ]);
    }

    public function validerDemandeEtLancerEnquete(Request $request, $demandeId)
    {
        // 1. Récupérer la demande d'affectation
        $demande = DemandeAffectation::findOrFail($demandeId);

        // 2. Valider la demande (ex. : mettre à jour son statut)
        $demande->update([
            'statut' => 'Validee',
        ]);

        // 3. Sélectionner un agent QHSE disponible
        $agentQhse = User::whereHas('roles', function ($q) {
            $q->where('nom', 'Agent QHSE');
        })->inRandomOrder()->first(); // ou selon une logique métier

        if (!$agentQhse) {
            return response()->json(['message' => 'Aucun agent QHSE disponible'], 404);
        }

        // 4. Lancer l’enquête QHSE en appelant la méthode du modèle
        $enquete = $demande->initierEnqueteQHSE($agentQhse->id);

        return response()->json([
            'message' => 'Demande validée et enquête QHSE lancée',
            'enquete' => $enquete,
        ]);
    }

    public function getEtudiantsNonAffectes()
{
    $etudiants = User::whereNull('id_affectation')->get();
    return response()->json($etudiants); // important
}

}

