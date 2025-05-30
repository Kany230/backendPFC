<?php

namespace App\Http\Controllers\Gestionnaire;

use App\Http\Controllers\Controller;
use App\Models\DemandeAffectation;
use App\Models\Reclamation;
use App\Models\User;
use App\Mail\AffectationAcceptee;
use App\Mail\AffectationRefusee;
use App\Mail\ReclamationAssignee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    public function index()
    {
        $demandes_affectation = DemandeAffectation::with(['commercant', 'local', 'rapport_qhse'])
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->get();

        $reclamations = Reclamation::with('user')
            ->whereHas('user', function($query) {
                $query->where('role', 'commercant');
            })
            ->whereIn('statut', ['nouveau', 'en_cours'])
            ->orderBy('created_at', 'desc')
            ->get();

        $techniciens = User::where('role', 'technicien')
            ->withCount(['interventions' => function($query) {
                $query->where('statut', 'en_cours');
            }])
            ->get()
            ->map(function($technicien) {
                $technicien->disponible = $technicien->interventions_count < 3;
                return $technicien;
            });

        return view('gestionnaire.dashboard', compact(
            'demandes_affectation',
            'reclamations',
            'techniciens'
        ));
    }

    public function validerAffectation(DemandeAffectation $demande)
    {
        if (!$demande->rapport_qhse || $demande->rapport_qhse->conclusion !== 'favorable') {
            return redirect()
                ->route('gestionnaire.dashboard')
                ->with('error', 'Cette demande ne peut pas être validée sans un rapport QHSE favorable.');
        }

        $demande->update(['statut' => 'acceptee']);
        
        // Mettre à jour le statut du local
        $demande->local->update(['statut' => 'occupe']);
        
        // Créer un contrat ou une affectation
        $demande->commercant->locaux()->attach($demande->local_id, [
            'date_debut' => now(),
            'statut' => 'actif'
        ]);

        // Envoyer l'email au commerçant
        Mail::to($demande->commercant->email)
            ->send(new AffectationAcceptee($demande));

        return redirect()
            ->route('gestionnaire.dashboard')
            ->with('success', 'La demande d\'affectation a été acceptée.');
    }

    public function refuserAffectation(DemandeAffectation $demande)
    {
        $demande->update(['statut' => 'refusee']);

        // Envoyer l'email au commerçant
        Mail::to($demande->commercant->email)
            ->send(new AffectationRefusee($demande));

        return redirect()
            ->route('gestionnaire.dashboard')
            ->with('success', 'La demande d\'affectation a été refusée.');
    }

    public function assignerReclamation(Request $request, Reclamation $reclamation)
    {
        $validated = $request->validate([
            'technicien_id' => 'required|exists:users,id',
            'priorite' => 'required|in:basse,moyenne,haute,urgente',
            'commentaire' => 'nullable|string'
        ]);

        $technicien = User::findOrFail($validated['technicien_id']);
        
        // Vérifier si le technicien est disponible
        $interventions_en_cours = $technicien->interventions()
            ->where('statut', 'en_cours')
            ->count();
            
        if ($interventions_en_cours >= 3) {
            return redirect()
                ->route('gestionnaire.dashboard')
                ->with('error', 'Ce technicien a déjà trop d\'interventions en cours.');
        }

        // Créer l'intervention
        $intervention = $reclamation->interventions()->create([
            'technicien_id' => $validated['technicien_id'],
            'priorite' => $validated['priorite'],
            'commentaire' => $validated['commentaire'],
            'statut' => 'en_cours',
            'date_debut' => now()
        ]);

        // Mettre à jour le statut de la réclamation
        $reclamation->update(['statut' => 'en_cours']);

        // Envoyer l'email au technicien
        Mail::to($technicien->email)
            ->send(new ReclamationAssignee($intervention));

        return redirect()
            ->route('gestionnaire.dashboard')
            ->with('success', 'La réclamation a été assignée au technicien.');
    }
} 