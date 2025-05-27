<?php

namespace App\Http\Controllers;

use App\Mail\NouveauMotDePasse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function login(Request $request){

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        if(!Auth::attempt($credentials)){
            return response()->json([
                'status' => 'error',
                'message' => 'Identifiants incorrect'
            ],401);
        }

          /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();
        if($utilisateur->statut !== 'Actif'){
            Auth::logout();
            return response()->json([
                'status' => 'error',
                'message' => 'Compte n\'est pas actif'
            ], 403);
        }

        $token = $utilisateur->createToken('auth-token')->plainTextToken;

return response()->json([
    'user' => $utilisateur,
    'token' => $token,
    'token_type' => 'Bearer',
]);
    }

    public function register(Request $request){

        $data = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'sexe' => 'required|in:F,H',
            'adresse' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
            'role' => 'required|string|in:etudiant,commercant',
            'photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('photos', $filename, 'public');
            $data['photo'] = $path; 
        }

        $data['password'] = Hash::make($data['password']);
        $data['role'] = null;
        $data['statut'] = 'Inactif';

        /** @var \App\Models\User$user **/
        $utilisateur = User::create($data);

        $token = $utilisateur->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Inscription réussie, en attente de validation par un administrateur.',
            'data' => [
               'utilisateur' => $utilisateur,
               'token' => $token,
               'token_type' => 'Bearer'
        ]
    ], 201);

    }

    public function logout(){

        /** @var \App\Models\User$user **/
        $user = Auth::user();
        
        $user->currentAccessToken()->delete();

        return response()->json([
            'status'=> 'success',
            'message' => 'Deconnexion reussie'
        ]);
    }

    public function me(){

        /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();

        return response()->json([
            'status' => 'success',
            'data' => $utilisateur
        ]);
    }

    public function refresh(){

        /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();

        $utilisateur->currentAccessToken()->delete();

        $newToken = $utilisateur->createToken('auth-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $newToken,
                'token_type' => 'Bearer'
            ]
        ]);
    }

    public function reinitialiserPassword(Request $request){

        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $utilisateur = User::where('email', $request->input('email'))->first();

        $newPassword = Str::random(8);

        $utilisateur->update([
            'password' => Hash::make($newPassword)
        ]);

        Mail::to($utilisateur->email)->send(new NouveauMotDePasse($newPassword));

        return response()->json([
            'status' => 'success',
            'message' => 'un nouveau mot de passe a ete envoyer dans votre email'
        ]);
    }
    
    public function changerPassword(Request $request){

        $request->validate([
            'ancien_password' => 'required',
            'new_password' => 'reuired|string|min:8|confirmed'
        ]);

       /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();

        if(!Hash::check($request->input('ancien_password'), $utilisateur->password)){
            return response()->json([
                'status' => 'error',
                'message' => 'mot de passe incorrect'
            ]);
        }

        $utilisateur->update([
            'password' => Hash::make($request->input('new_password'))
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Mot de passe mis a jour'
        ]);


    }
}
