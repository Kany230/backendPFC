<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\CompteValide;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function listeEnAttente()
    {
        $utilisateurs = User::where('statut', 'Inactif')
            ->whereIn('role', ['etudiant', 'commercant'])
            ->get();

        return response()->json($utilisateurs);
    }

    public function validerInscription($id)
    {
        $user = User::findOrFail($id);
        $user->statut = 'Actif';
        $user->save();

        // Envoyer l'email de confirmation
        Mail::to($user->email)->send(new CompteValide($user));

        return response()->json([
            'message' => 'Compte validé avec succès',
            'user' => $user
        ]);
    }

    public function ajouterGestionnaire(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'sexe' => 'required|in:F,H',
            'telephone' => 'required|string',
            'adresse' => 'required|string',
        ]);

        $gestionnaire = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sexe' => $request->sexe,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'role' => 'gestionnaire',
            'statut' => 'Actif',
        ]);

        return response()->json([
            'message' => 'Gestionnaire ajouté avec succès',
            'user' => $gestionnaire
        ], 201);
    }
}
