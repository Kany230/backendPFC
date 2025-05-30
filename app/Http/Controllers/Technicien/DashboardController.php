<?php

namespace App\Http\Controllers\Technicien;

use App\Http\Controllers\Controller;
use App\Models\Reclamation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $technicien = Auth::user();
        
        // Réclamations en cours
        $reclamations_en_cours = Reclamation::with(['local.batiment', 'commercant', 'etudiant'])
            ->where('technicien_id', $technicien->id)
            ->where('statut', 'en_cours')
            ->orderBy('created_at', 'desc')
            ->get();

        // Réclamations terminées
        $reclamations_terminees = Reclamation::with(['local.batiment', 'commercant', 'etudiant'])
            ->where('technicien_id', $technicien->id)
            ->where('statut', 'termine')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('technicien.dashboard', compact(
            'reclamations_en_cours',
            'reclamations_terminees'
        ));
    }

    public function terminerReclamation(Request $request, Reclamation $reclamation)
    {
        // Vérifier que la réclamation est bien assignée à ce technicien
        if ($reclamation->technicien_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'commentaire' => 'required|string|max:500'
        ]);

        // Mettre à jour le statut de la réclamation
        $reclamation->update([
            'statut' => 'termine',
            'date_resolution' => now()
        ]);

        // Créer un message pour le gestionnaire/chef de pavillon
        Message::create([
            'expediteur_id' => Auth::id(),
            'destinataire_id' => $reclamation->assigne_par,
            'reclamation_id' => $reclamation->id,
            'contenu' => $request->commentaire,
            'type' => 'resolution'
        ]);

        return redirect()
            ->route('technicien.dashboard')
            ->with('success', 'La réclamation a été marquée comme terminée et le responsable a été notifié.');
    }

    public function showReclamation(Reclamation $reclamation)
    {
        // Vérifier que la réclamation est bien assignée à ce technicien
        if ($reclamation->technicien_id !== Auth::id()) {
            abort(403);
        }

        $reclamation->load(['local.batiment', 'commercant', 'etudiant', 'messages']);

        return view('technicien.reclamations.show', compact('reclamation'));
    }
} 