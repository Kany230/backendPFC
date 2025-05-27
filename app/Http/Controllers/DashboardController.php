<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Batiment;
use App\Models\DemandeAffectation;
use App\Models\Local;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
      
    public function getStatistiques(){

        $role = Auth::user()->role;
        if($role != 'admin'){
            return response()->json([
                'message' => 'Access non autorise'
            ], 404);
        }
        $stats = [
            'batiments' => [
                'total' => Batiment::count(),
                'avec_locaux_libres' => Batiment::whereHas('locaux', function($q) {
                    $q->where('disponible', true);
                })->count()
            ],
            'locaux' => [
                'total' => Local::count(),
                'occupes' => Local::where('disponible', false)->count(),
                'libres' => Local::where('disponible', true)->count(),
                'taux_occupation' => round((Local::where('disponible', false)->count() / max(Local::count(), 1)) * 100, 2)
            ],
            'affectations' => [
                'actives' => Affectation::where('statut', 'Active')->count(),
                'expirant_bientot' => Affectation::where('statut', 'Active')
                    ->whereBetween('date_fin', [now(), now()->addDays(30)])
                    ->count(),
                'ce_mois' => Affectation::whereMonth('date_debut', now()->month)
                    ->whereYear('date_debut', now()->year)
                    ->count()
            ],
            'demandes' => [
                'en_attente' => DemandeAffectation::where('statut', 'En attente')->count(),
                'ce_mois' => DemandeAffectation::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count()
            ],
            'revenus' => [
                'mensuel' => Affectation::where('statut', 'Active')->sum('loyer_mensuel'),
                'annuel_estime' => Affectation::where('statut', 'Active')->sum('loyer_mensuel') * 12
            ]
        ];
        
        return response()->json([
            'status' => 'success',
            'data' => $stats
        ]);
    }
    
    //Récupérer les affectations récentes
    public function getAffectationsRecentes(Request $request)
    {
        $limite = $request->input('limite', 10);
        
        $affectations = Affectation::with(['utilisateur', 'local.batiment'])
            ->orderBy('created_at', 'desc')
            ->limit($limite)
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $affectations
        ]);
    }
    
    //Récupérer les demandes en attente
    public function getDemandesEnAttente(Request $request)
    {
        $limite = $request->input('limite', 10);
        
        $demandes = DemandeAffectation::with('utilisateur')
            ->where('statut', 'En attente')
            ->orderBy('created_at', 'asc')
            ->limit($limite)
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $demandes
        ]);
    }
    
    //Récupérer l'évolution des revenus
    public function getEvolutionRevenus(Request $request)
    {
        $periode = $request->input('periode', 12); // 12 derniers mois par défaut
        
        $revenus = collect();
        
        for ($i = $periode - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            
            $revenus->push([
                'mois' => $date->format('Y-m'),
                'libelle' => $date->format('M Y'),
                'montant' => Affectation::where('statut', 'Active')
                    ->where('date_debut', '<=', $date->endOfMonth())
                    ->where('date_fin', '>=', $date->startOfMonth())
                    ->sum('loyer_mensuel')
            ]);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $revenus
        ]);
    }
    
    //Récupérer les alertes système
     
    public function getAlertes()
    {
        $alertes = collect();
        
        // Affectations expirant bientôt
        $affectationsExpirant = Affectation::with(['utilisateur', 'local'])
            ->where('statut', 'Active')
            ->whereBetween('date_fin', [now(), now()->addDays(30)])
            ->get();
        
        foreach ($affectationsExpirant as $affectation) {
            $alertes->push([
                'type' => 'expiration',
                'niveau' => 'warning',
                'message' => "L'affectation du local {$affectation->local->nom} expire le " . 
                           Carbon::parse($affectation->dateFin)->format('d/m/Y'),
                'lien' => "/affectations/{$affectation->id}"
            ]);
        }
        
        // Demandes en attente depuis plus de 7 jours
        $demandesAnciennes = DemandeAffectation::with('utilisateur')
            ->where('statut', 'En attente')
            ->where('created_at', '<', now()->subDays(7))
            ->get();
        
        foreach ($demandesAnciennes as $demande) {
            $alertes->push([
                'type' => 'demande_ancienne',
                'niveau' => 'info',
                'message' => "Demande de {$demande->utilisateur->nom} en attente depuis " . 
                           $demande->created_at->diffForHumans(),
                'lien' => "/demandes/{$demande->id}"
            ]);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $alertes->sortBy('niveau')
        ]);
    }
}
