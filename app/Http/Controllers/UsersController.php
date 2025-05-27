<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
}
