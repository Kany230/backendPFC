<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\DemandeAffectation;
use App\Models\Local;
use App\Models\Paiement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RapportController extends Controller
{

    //Rapport d'occupation des locaux
    public function rapportOccupation(Request $request){

        $batimentId = $request->input('id_batiment');

        $query = Local::with(['batiment', 'affectaion' => function($q){
            $q->where('statut', 'active');
        }]);

        if($batimentId){
            $query->where('id_batiment', $batimentId);
        }

        $locaux = $query->get();

        $donnees = $locaux->map(function($local){
            $affectation = $local->affectations->first();

            return [
                'local' => $local,
                'batiment' => $local->batiment->nom,
                'statut' => $local->disponible ? 'Libre' : 'Occupe',
                'occupant' => $affectation ? $affectation->utilisateur->nom . ' ' .
                             $affectation->utilisateur->prenom : null,
                'loyer' => $affectation ? $affectation->loyer_mensuel : null,
                'dateDebut' => $affectation ? $affectation->dateDebut : null,
                'dateFin' => $affectation ? $affectation->dateFin : null
            ];
        });

        $resume = [
            'total_locaux' => $locaux->count(),
            'locaux_occupes' => $locaux->where('disponible', false)->count(),
            'locaux_libres' => $locaux->where('disponible', true)->count(),
            'taux_occupation' => $locaux->count() > 0 ?
                round(($locaux->where('disponible', false)->count() / $locaux->count()) * 100, 2) : 0,
            'revenus_mensuels' => $locaux->flatMap->affectations->sum('loyer_mensuels')
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'locaux' => $donnees,
                'resume' => $resume
            ]
            ]);
    }

    public function rapportFinancier(Request $request)
{
    $request->validate([
        'date_debut' => 'required|date',
        'date_fin' => 'required|date|after_or_equal:date_debut',
        'batiment_id' => 'nullable|exists:batiments,id'
    ]);

    $dateDebut = $request->input('date_debut');
    $dateFin = $request->input('date_fin');
    $batimentId = $request->input('batiment_id');

    $paiements = $this->getPaiements($dateDebut, $dateFin, $batimentId);
    $affectations = $this->getAffectations($dateDebut, $dateFin, $batimentId);
    $revenusAttendus = $this->calculerRevenusAttendus($affectations, $dateDebut, $dateFin);

    $parBatiment = $this->analyserParBatiment($paiements);
    $parMode = $this->analyserParModePaiement($paiements);

    $donnees = [
        'periode' => [
            'debut' => $dateDebut,
            'fin' => $dateFin
        ],
        'resume' => [
            'revenus_percus' => $paiements->sum('montant'),
            'revenus_attendus' => $revenusAttendus,
            'taux_recouvrement' => $revenusAttendus > 0 ?
                round(($paiements->sum('montant') / $revenusAttendus) * 100, 2) : 0,
            'nombre_paiements' => $paiements->count(),
            'paiement_moyen' => $paiements->avg('montant') ?? 0
        ],
        'par_batiment' => $parBatiment,
        'par_mode_paiement' => $parMode,
        'evolution_mensuelle' => $this->getEvolutionMensuelle($paiements, $dateDebut, $dateFin)
    ];

    return response()->json([
        'status' => 'success',
        'data' => $donnees
    ]);
}

private function getPaiements($dateDebut, $dateFin, $batimentId)
{
    $query = Paiement::with(['affectation.local.batiment'])
        ->whereBetween('date_paiement', [$dateDebut, $dateFin]);

    if ($batimentId) {
        $query->whereHas('affectation.local', function ($q) use ($batimentId) {
            $q->where('batiment_id', $batimentId);
        });
    }

    return $query->get();
}

private function getAffectations($dateDebut, $dateFin, $batimentId)
{
    $query = Affectation::with(['local.batiment'])
        ->where('statut', 'active')
        ->where('date_debut', '<=', $dateFin)
        ->where('date_fin', '>=', $dateDebut);

    if ($batimentId) {
        $query->whereHas('local', function ($q) use ($batimentId) {
            $q->where('batiment_id', $batimentId);
        });
    }

    return $query->get();
}

private function calculerRevenusAttendus($affectations, $dateDebut, $dateFin)
{
    $revenusAttendus = 0;
    foreach ($affectations as $affectation) {
        $debut = max(Carbon::parse($dateDebut), Carbon::parse($affectation->date_debut));
        $fin = min(Carbon::parse($dateFin), Carbon::parse($affectation->date_fin));

        $mois = $debut->diffInMonths($fin) + 1;
        $revenusAttendus += $affectation->loyer_mensuel * $mois;
    }

    return $revenusAttendus;
}

private function analyserParBatiment($paiements)
{
    return $paiements->groupBy('affectation.local.batiment.nom')->map(function ($group) {
        return [
            'nombre_paiements' => $group->count(),
            'montant_total' => $group->sum('montant'),
            'montant_moyen' => $group->avg('montant')
        ];
    });
}

private function analyserParModePaiement($paiements)
{
    return $paiements->groupBy('mode_paiement')->map(function ($group) use ($paiements) {
        return [
            'nombre' => $group->count(),
            'montant' => $group->sum('montant'),
            'pourcentage' => $paiements->count() > 0 ?
                round(($group->count() / $paiements->count()) * 100, 2) : 0
        ];
    });
}

    public function rapportDemandes(Request $request)
    {
        $request->validate([
            'dateDebut' => 'nullable|date',
            'dateFin' => 'nullable|date|after_or_equal:dateDebut'
        ]);
        
        $query = DemandeAffectation::with(['utilisateur']);
        
        if ($request->filled('dateDebut')) {
            $query->whereDate('created_at', '>=', $request->input('dateDebut'));
        }
        
        if ($request->filled('dateFin')) {
            $query->whereDate('created_at', '<=', $request->input('dateFin'));
        }
        
        $demandes = $query->get();
        
        // Statistiques par statut
        $parStatut = $demandes->groupBy('statut')->map(function($group) use ($demandes) {
            return [
                'nombre' => $group->count(),
                'pourcentage' => $demandes->count() > 0 ? 
                    round(($group->count() / $demandes->count()) * 100, 2) : 0
            ];
        });
        
        // Statistiques par type de local demandé
        $parType = $demandes->groupBy('type_local_souhaite')->map(function($group) use ($demandes) {
            return [
                'nombre' => $group->count(),
                'pourcentage' => $demandes->count() > 0 ? 
                    round(($group->count() / $demandes->count()) * 100, 2) : 0
            ];
        });
        
        // Temps de traitement moyen
        $demandesTraitees = $demandes->whereNotNull('traite_le');
        $tempsTraitement = $demandesTraitees->map(function($demande) {
            return Carbon::parse($demande->created_at)->diffInDays(Carbon::parse($demande->traite_le));
        })->avg();
        
        $donnees = [
            'resume' => [
                'total_demandes' => $demandes->count(),
                'en_attente' => $demandes->where('statut', 'en_attente')->count(),
                'approuvees' => $demandes->where('statut', 'approuvee')->count(),
                'rejetees' => $demandes->where('statut', 'rejetee')->count(),
                'temps_traitement_moyen' => round($tempsTraitement ?? 0, 1) . ' jours'
            ],
            'par_statut' => $parStatut,
            'par_type_local' => $parType,
            'evolution_mensuelle' => $this->getEvolutionDemandesParMois($demandes)
        ];
        
        return response()->json([
            'status' => 'success',
            'data' => $donnees
        ]);
    }
    
    /**
     * Calculer l'évolution mensuelle des paiements
     */
    private function getEvolutionMensuelle($paiements, $dateDebut, $dateFin)
    {
        $debut = Carbon::parse($dateDebut)->startOfMonth();
        $fin = Carbon::parse($dateFin)->endOfMonth();
        
        $evolution = collect();
        
        while ($debut <= $fin) {
            $moisPaiements = $paiements->filter(function($paiement) use ($debut) {
                return Carbon::parse($paiement->date_paiement)->isSameMonth($debut);
            });
            
            $evolution->push([
                'mois' => $debut->format('Y-m'),
                'libelle' => $debut->format('M Y'),
                'nombre' => $moisPaiements->count(),
                'montant' => $moisPaiements->sum('montant')
            ]);
            
            $debut->addMonth();
        }
        
        return $evolution;
    }
    
    /**
     * Calculer l'évolution des demandes par mois
     */
    private function getEvolutionDemandesParMois($demandes)
    {
        return $demandes->groupBy(function($demande) {
            return Carbon::parse($demande->created_at)->format('Y-m');
        })->map(function($group, $mois) {
            return [
                'mois' => $mois,
                'libelle' => Carbon::parse($mois)->format('M Y'),
                'total' => $group->count(),
                'approuvees' => $group->where('statut', 'approuvee')->count(),
                'rejetees' => $group->where('statut', 'rejetee')->count(),
                'en_attente' => $group->where('statut', 'en_attente')->count()
            ];
        })->sortBy('mois')->values();
    }
}
 