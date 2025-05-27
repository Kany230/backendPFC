<?php

namespace App\Http\Controllers;

use App\Mail\ValidationCompteMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
     public function listeEnAttente()
    {
        $users = User::where('statut', 'Inactif')->get();

        return response()->json($users);
    }

    public function validerInscription(Request $request, $id){

        $request->validate([
            'statut' => 'required|string|in:Actif,Refusé',
        ]);

        $user = User::findOrFail($id);
        $user->role = $request->role;
        $user->statut = $request->statut;
         $user->save();

       
       Mail::to($user->email)->send(new ValidationCompteMail($user->statut));

       return response()->json(['message' => 'Utilisateur mis à jour et email envoyé.']);

    }

}
