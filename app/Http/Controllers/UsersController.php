<?php

namespace App\Http\Controllers;

use App\Models\Local;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use function Laravel\Prompts\password;

class UsersController extends Controller
{
public function index(Request $request)
{
    $roleRecherche = $request->input('role'); // par exemple 'admin'

    $usersQuery = User::query();

    if ($roleRecherche) {
        $usersQuery->where('role', $roleRecherche);
    }

    $users = $usersQuery->get();

     return response()->json([
        'status' => 'success',
        'data' => $users
    ]);

}

// Ajoutez cette méthode dans votre UsersController ou LocalController

 public function getEtudiantsEnPavillon() 
    {
        try {
            // VERSION CORRIGÉE avec debug
            $etudiants = User::where('role', 'etudiant')
                ->whereHas('affectations', function($query) { // Utilisez 'affectations' (pluriel)
                    $query->where('statut', 'Active')
                          ->whereHas('chambre', function($subQuery) {
                              $subQuery->whereHas('local', function($localQuery) {
                                  $localQuery->where('type', 'Pavillon'); // Attention à la casse
                              });
                          });
                })
                ->with([
                    'affectations' => function($query) {
                        $query->where('statut', 'Active')
                              ->whereDate('dateFin', '>=', now())
                              ->with(['chambre.local']);
                    }
                ])
                ->get();

            $result = [];

            foreach ($etudiants as $etudiant) {
                // Trouver l'affectation active dans un pavillon
                $affectationActive = $etudiant->affectations
                    ->where('statut', 'Active')
                    ->filter(function($affectation) {
                        return $affectation->dateFin >= now() &&
                               $affectation->chambre &&
                               $affectation->chambre->local &&
                               $affectation->chambre->local->type === 'Pavillon';
                    })
                    ->first();

                if ($affectationActive) {
                    $result[] = [
                        'id_utilisateur' => $etudiant->id,
                        'nom' => $etudiant->nom,
                        'prenom' => $etudiant->prenom,
                        'email' => $etudiant->email,
                        'telephone' => $etudiant->telephone,
                        'sexe' => $etudiant->sexe,
                        'statut_etudiant' => $etudiant->statut,
                        'id_local' => $affectationActive->chambre->local->id,
                        'pavillon_nom' => $affectationActive->chambre->local->nom,
                        'id_chambre' => $affectationActive->chambre->id,
                        'chambre_nom' => $affectationActive->chambre->nom,
                        'numero_chambre' => $affectationActive->chambre->numero,
                        'date_debut_affectation' => $affectationActive->dateDebut,
                        'date_fin_affectation' => $affectationActive->dateFin,
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Étudiants en pavillon récupérés avec succès',
                'total' => count($result),
                'data' => $result,
            ]);

        } catch (\Exception $e) {
               
           Log::error("Erreur getEtudiantsEnPavillon: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage(),
                'data' => [],
                'total' => 0
            ], 500);
        }
    }


// Alternative plus optimisée avec une requête directe
    public function getEtudiantsEnPavillonOptimise()
    {
        try {
            $etudiants = DB::table('users as u')
                ->join('affectations as a', 'u.id', '=', 'a.id_utilisateur')
                ->join('chambres as c', 'a.id_chambre', '=', 'c.id')
                ->join('locaux as l', 'c.id_local', '=', 'l.id')
                ->select([
                    'u.id as id_utilisateur',
                    'u.nom',
                    'u.prenom',
                    'u.email',
                    'u.telephone',
                    'u.sexe',
                    'u.statut as statut_etudiant',
                    'l.id as id_local',
                    'l.nom as pavillon_nom',
                    'c.id as id_chambre',
                    'c.numero as numero_chambre',
                    'a.dateDebut as date_debut_affectation',
                    'a.dateFin as date_fin_affectation'
                ])
                ->where('u.role', 'etudiant')
                ->where('a.statut', 'Active')
                ->where('l.type', 'Pavillon') // ou 'pavillon' selon vos données
                ->orderBy('u.nom')
                ->orderBy('u.prenom')
                ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Liste des étudiants en pavillon récupérée',
                'total' => $etudiants->count(),
                'data' => $etudiants
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur getEtudiantsEnPavillonOptimise: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage(),
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    

// Méthode pour obtenir les étudiants d'un pavillon spécifique
public function getEtudiantsDuPavillon($pavillonId)
{
    $pavillon = Local::where('id', $pavillonId)
        ->where('type', 'Pavillon')
        ->first();

    if (!$pavillon) {
        return response()->json([
            'status' => 'error',
            'message' => 'Pavillon non trouvé'
        ], 404);
    }

    $etudiants = User::where('role', 'etudiant')
        ->whereHas('affectations', function ($query) use ($pavillonId) {
            $query->where('statut', 'Active')
                  ->whereDate('dateFin', '>=', now())
                  ->whereHas('chambre', function ($subQuery) use ($pavillonId) {
                      $subQuery->where('id_local', $pavillonId);
                  });
        })
        ->with([
            'affectations' => function ($query) use ($pavillonId) {
                $query->where('statut', 'Active')
                      ->whereDate('dateFin', '>=', now())
                      ->whereHas('chambre', function ($subQuery) use ($pavillonId) {
                          $subQuery->where('id_local', $pavillonId);
                      })
                      ->with('chambre');
            }
        ])
        ->get();

    $result = [];
    
    foreach ($etudiants as $etudiant) {
        foreach ($etudiant->affectations as $affectation) {
            if ($affectation->chambre && $affectation->chambre->id_local == $pavillonId) {
                $result[] = [
                    'etudiant_id' => $etudiant->id,
                    'nom' => $etudiant->nom,
                    'prenom' => $etudiant->prenom,
                    'email' => $etudiant->email,
                    'telephone' => $etudiant->telephone,
                    'sexe' => $etudiant->sexe,
                    'chambre_id' => $affectation->chambre->id,
                    'chambre_nom' => $affectation->chambre->nom,
                    'numero_chambre' => $affectation->chambre->numero,
                    'date_debut_affectation' => $affectation->dateDebut,
                    'date_fin_affectation' => $affectation->dateFin,
                    'statut_etudiant' => $etudiant->statut
                ];
            }
        }
    }

    return response()->json([
        'status' => 'success',
        'message' => "Liste des étudiants du pavillon {$pavillon->nom}",
        'pavillon' => [
            'id' => $pavillon->id,
            'nom' => $pavillon->nom
        ],
        'total_etudiants' => count($result),
        'data' => $result
    ]);
}




    public function store(Request $request){

        $request->validate([
            'nom' => 'required|string|max:255', 
            'prenom' => 'required|string|max:255', 
            'email' => 'required|email|max:255', 
            'sexe' => 'required|in:F,H',
            'telephone' => 'rquired|string|max:20', 
            'adresse' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed', 
            'statut'=> 'required|in:Actif,Inactif,Suspendu',
            'photo' => 'nullable|image|max:2048',
            'role'=> 'nullable|string'
        ]);

        $data = $request->only(['nom', 'prenom','email', 'telephone', 'adresse', 'statut']);
        $data['password'] = Hash::make($request->input('password'));

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {

            $chemin = $request->file('photo')->store('photos_utilisateurs', 'public');
            $data['photo'] = $chemin;
        }

        $utilisateur = User::create($data);

        if($request->filled('role')){
            $utilisateur->assignRole($request->input('role'));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'utilisateur cree',
            'data' => $utilisateur
        ], 201  );
    }

    public function show($id){

        $utilisateur = User::with(['roles', 'permissions'])->find($id);

        if(!$utilisateur){
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouve'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $utilisateur
        ]);
    }

    public function update(Request $request, $id){

        $utilisateur = User::find($id);

        if (!$utilisateur) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouve'
            ], 404);
        }

        $request->validate([
            'nom'=> 'sometimes|string|max:255', 
            'prenom' => 'sometimes|string|max:255',  
            'sexe' => 'sometimes|in:F,H',
            'telephone' => 'sometimes|string|max:20', 
            'adresse' => 'sometimes|string|max:500',
            'password' => 'sometimes|string|min:8|confirmed', 
            'statut' => 'sometimes|in:actif,inactif',
            'role' => 'nullable|string' 
        ]);

         /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();

        $data = $request->only(['nom', 'prenom', 'sexe','telephone','adresse', 'statut']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $utilisateur->update($data);

        if ($request->filled('role')) {
            $utilisateur->syncRoles([$request->input('role')]);
        }

        return response()->json([
            'status' => 'Mis a jour termine',
            'data' => $utilisateur
        ]);
    }

    public function destroy($id){

        /** @var \App\Models\User$user **/
        $user = Auth::user();

        if($id == $user->id){
            return response()->json([
                'status' => 'error',
                'message' => 'impossible de supprimer son prpre compte'
            ], 400);
        }

        $utilisateur = User::find($id);

        if(!$utilisateur){
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisareur introuvable'
            ], 404);
        }

        $utilisateur->delete();


        return response()->json([
            'status' => 'success',
            'message' => 'Utilisateur supprime'
        ]);
    }

    public function getProfil(){
        
         /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();

        $utilisateur->load(['roles', 'permissions']);

        return response()->json([
            'status' => 'success',
            'data' => $utilisateur
        ]);
    }

    public function updateProfil(Request $request){

        $request->validate([
            'nom'=> 'required|string|max:255', 
            'prenom' => 'required|string|max:255',  
            'sexe' => 'nullable|in:F,H',
            'telephone' => 'nullable|string|max:20', 
            'adresse' => 'nullable|string|max:500',
            'password' => 'nullable|string|min:8|confirmed', 
            'photo' => 'nullable|image|max:2048'
        ]);

         /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();

        $data = $request->only(['nom', 'prenom', 'sexe','telephone','adresse']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            // Optionnel : supprimer l'ancienne photo si besoin
            Storage::delete($utilisateur->photo);

            $chemin = $request->file('photo')->store('photos_utilisateurs', 'public');
            $data['photo'] = $chemin;
        }

        $utilisateur->update($data);

        return response()->json([
            'status' => 'Mis a jour termine',
            'data' => $utilisateur
        ]);
    }

    public function changerStatut($id){

        $utilisateur = User::find($id);

        if(!$utilisateur){
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouve'
            ], 404);
        }

         /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();

        if($id == $utilisateur->id){

            return response()->json([
                'status' => 'error',
                'message' => 'Vous ne pouvez pas modifier votre propre compte'
            ], 400);
        }

        if ($utilisateur->statut === 'Actif') {
            $utilisateur->statut = 'Inactif';
        } elseif ($utilisateur->statut === 'Inactif') {
            $utilisateur->statut = 'Suspendu';
        } else {
            $utilisateur->statut = 'Actif';
        }
        $utilisateur->save();

        switch (strtolower($utilisateur->statut)) {
            case 'actif':
                $message = 'Utilisateur activé';
                break;
            case 'inactif':
                $message = 'Utilisateur désactivé';
                break;
            case 'suspendu':
                $message = 'Utilisateur suspendu';
                break;
            default:
                $message = 'Statut inconnu';
                break;
        }



        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $utilisateur
        ]);
    }

       public function debugEtudiantsPavillon()
    {
        try {
            // Test 1: Vérifier les étudiants
            $totalEtudiants = User::where('role', 'etudiant')->count();
            
            // Test 2: Vérifier les affectations actives
            $affectationsActives = DB::table('affectations')
                ->where('statut', 'Active')
                ->whereDate('dateFin', '>=', now())
                ->count();
            
            // Test 3: Vérifier les pavillons
            $pavillons = DB::table('locaux')
                ->where('type', 'LIKE', '%pavillon%') // Test avec différentes casses
                ->orWhere('type', 'LIKE', '%Pavillon%')
                ->get();
            
            // Test 4: Vérifier les relations
            $sampleData = DB::table('users as u')
                ->join('affectations as a', 'u.id', '=', 'a.id_utilisateur')
                ->join('chambres as c', 'a.id_chambre', '=', 'c.id')
                ->join('locaux as l', 'c.id_local', '=', 'l.id')
                ->select('u.nom', 'u.prenom', 'l.nom as local_nom', 'l.type', 'a.statut', 'a.dateFin')
                ->where('u.role', 'etudiant')
                ->limit(5)
                ->get();

            return response()->json([
                'debug_info' => [
                    'total_etudiants' => $totalEtudiants,
                    'affectations_actives' => $affectationsActives,
                    'pavillons_trouves' => $pavillons,
                    'sample_data' => $sampleData,
                    'tables_structure' => [
                        'users' => DB::getSchemaBuilder()->getColumnListing('users'),
                        'affectations' => DB::getSchemaBuilder()->getColumnListing('affectations'),
                        'chambres' => DB::getSchemaBuilder()->getColumnListing('chambres'),
                        'locaux' => DB::getSchemaBuilder()->getColumnListing('locaux'),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    // ... autres méthodes existantes ...

}
