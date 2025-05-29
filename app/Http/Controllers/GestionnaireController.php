<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class GestionnaireController extends Controller
{
    public function ajouterPersonnel(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'sexe' => 'required|in:F,H',
            'telephone' => 'required|string',
            'adresse' => 'required|string',
            'role' => 'required|in:chefpavillon,agentQHSE,technicien',
        ]);

        $personnel = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sexe' => $request->sexe,
            'telephone' => $request->telephone,
            'adresse' => $request->adresse,
            'role' => $request->role,
            'statut' => 'Actif',
        ]);

        return response()->json([
            'message' => 'Personnel ajouté avec succès',
            'user' => $personnel
        ], 201);
    }

    public function getPersonnel()
    {
        $personnel = User::whereIn('role', ['chefpavillon', 'agentQHSE', 'technicien'])
            ->get();

        return response()->json($personnel);
    }

    public function getChefsPavillon()
    {
        $chefs = User::where('role', 'chefpavillon')->get();
        return response()->json($chefs);
    }

    public function getAgentsQHSE()
    {
        $agents = User::where('role', 'agentQHSE')->get();
        return response()->json($agents);
    }

    public function getTechniciens()
    {
        $techniciens = User::where('role', 'technicien')->get();
        return response()->json($techniciens);
    }
} 