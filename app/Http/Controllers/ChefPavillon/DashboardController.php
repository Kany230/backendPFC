<?php

namespace App\Http\Controllers\ChefPavillon;

use App\Http\Controllers\Controller;
use App\Models\DemandeAffectationChambre;
use App\Models\Reclamation;
use App\Models\User;
use App\Mail\ChambreAffectationAcceptee;
use App\Mail\ChambreAffectationRefusee;
use App\Mail\ReclamationAssignee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    public function index()
    {
        $demandes_affectation = DemandeAffectationChambre::with(['etudiant', 'chambre'])
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->get();

        $reclamations = Reclamation::with(['etudiant', 'chambre'])
            ->whereHas('etudiant', function($query) {
                $query->where('role', 'etudiant');
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

        return view('chef-pavillon.dashboard', compact(
            'demandes_affectation',
            'reclamations',
            'techniciens'
        ));
    }

    public function validerAffectation(DemandeAffectationChambre $demande)
    {
        // Vérifier si la chambre n'est pas pleine
        if ($demande->chambre->occupants_count >= 8) {
            return redirect()
                ->route('chef-pavillon.dashboard')
                ->with('error', 'Cette chambre est déjà pleine.');
        }

        $demande->update(['statut' => 'acceptee']);
        
        // Mettre à jour l'occupation de la chambre
        $demande->chambre->etudiants()->attach($demande->etudiant_id, [
            'date_debut' => now(),
            'statut' => 'actif'
        ]);

        // Envoyer l'email à l'étudiant
        Mail::to($demande->etudiant->email)
            ->send(new ChambreAffectationAcceptee($demande));

        return redirect()
            ->route('chef-pavillon.dashboard')
            ->with('success', 'La demande d\'affectation a été acceptée.');
    }

    public function refuserAffectation(DemandeAffectationChambre $demande)
    {
        $demande->update(['statut' => 'refusee']);

        // Envoyer l'email à l'étudiant
        Mail::to($demande->etudiant->email)
            ->send(new ChambreAffectationRefusee($demande));

        return redirect()
            ->route('chef-pavillon.dashboard')
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
                ->route('chef-pavillon.dashboard')
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
            ->route('chef-pavillon.dashboard')
            ->with('success', 'La réclamation a été assignée au technicien.');
    }
} 