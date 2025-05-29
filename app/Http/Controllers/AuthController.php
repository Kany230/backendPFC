<?php

namespace App\Http\Controllers;

use App\Mail\NouveauMotDePasse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request){

        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Les informations de connexion sont incorrectes.'],
            ]);
        }

        /** @var \App\Models\User$user **/
        $user = Auth::user();
        if($user->statut !== 'Actif'){
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['Votre compte n\'est pas encore activé.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'role' => $user->role
        ]);
    }

    public function register(Request $request){

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'sexe' => 'required|in:F,H',
            'telephone' => 'required|string',
            'adresse' => 'required|string',
            'role' => 'required|in:etudiant,commercant,admin,gestionnaire,technicien,agentQHSE,chefpavillon',
        ]);

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sexe' => $request->sexe,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'role' => $request->role,
            'statut' => 'Inactif',
        ]);

        return response()->json([
            'message' => 'Inscription réussie. En attente de validation.',
            'user' => $user
        ], 201);
    }

    public function logout(Request $request){

        /** @var \App\Models\User$user **/
        $user = Auth::user();
        
        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

    public function me(Request $request){

        /** @var \App\Models\User$user **/
        $user = Auth::user();

        return response()->json([
            'user' => $user,
            'role' => $user->role
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
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email.'
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
    
    public function changerPassword(Request $request){

        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

       /** @var \App\Models\User$user **/
        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Le mot de passe actuel est incorrect.'],
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Mot de passe modifié avec succès'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Mot de passe réinitialisé avec succès'
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
