<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Batiment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'adresse',
        'superficie',
        'description',
        'dateConstruction',
        'localisation_lat', 
        'localisation_lng',
    ];

    protected $dates = [
        'dateConstruction'
    ];

    public function locaux(){
        return $this->hasMany(Local::class);
    }

    public function cartographieElements(){
        return $this->hasMany(CartographieElement::class);
    }

    public function getStatiqueOccupation(){
       
        $total = $this->locaux()->count();
        $occupes = $this->locaux()->where('disponible', false)->count();
        $disponibles = $total - $occupes;

        return [
            'total' => $total,
            'occupes' => $occupes,
            'disponible' => $disponibles,
            'taux_occupation' => $total > 0 ? round(($occupes/$total) * 100, 2) : 0
        ];
    }

    public function getRevenus($dateDebut = null, $dateFin = null){
        $query = $this->locaux()
        ->join('affectations', 'locaux.id', '=', 'affectations.local.id')
        ->join('contrats', 'affectations.id', '=', 'contrats.affectation_id')
        ->join('paiements', 'contrats.id', '=', 'paiements.contrat_id')
        ->where('paiements.statut', 'Valide');

        if($dateDebut){
            $query->where('paiements.datePaiement', '>=', $dateDebut);
        }

        if($dateDebut){
            $query->where('paiements.datePaiement', '<=', $dateFin);
        }

        $total = $query->sum('paiments.montant');

        return $total;
    }

    public function getRevenusParMois($annee = null){
        $annee = $annee ?? date('Y');

        return  $this->locaux()
        ->join('affectations', 'locaux.id', '=', 'affectations.local.id')
        ->join('contrats', 'affectations.id', '=', 'contrats.affectation_id')
        ->join('paiements', 'contrats.id', '=', 'paiements.contrat_id')
        ->whereYear('paiements.datePaiement', $annee)
        ->where('paiements.statut', 'Valide')
        ->selectRaw('MONTH(paiments.datePaiement) as mois, SUM(paiements.montant) as total')
        ->groupByRaw('MONTH(paiemnts.datePaiement)')
        ->orderByRaw('MONTH(paiments.datePaiment)')
        ->pluck('total', 'mois')
        ->toArray();
    }

    public function getRevenusParAnnee(){
        
        return  $this->locaux()
        ->join('affectations', 'locaux.id', '=', 'affectations.local.id')
        ->join('contrats', 'affectations.id', '=', 'contrats.affectation_id')
        ->join('paiements', 'contrats.id', '=', 'paiements.contrat_id')
        ->where('paiements.statut', 'Valide')
        ->selectRaw('YEAR(paiments.datePaiement) as annee, SUM(paiements.montant) as total')
        ->groupByRaw('YEAR(paiemnts.datePaiement)')
        ->orderByRaw('YEAR(paiments.datePaiment)')
        ->pluck('total', 'annee')
        ->toArray();
    }


    public function getOccupants(){

        return User::whereHas('affectations', function($query){
            $query->whereHas('local', function($q){
                $q->where('id_batiment', $this->id);
            })->where('statut', 'Active');
        })->get();
    }
    
}
