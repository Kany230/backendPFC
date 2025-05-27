<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Local extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'locaux';

    protected $fillable = [
        'nom',
        'id_batiment',   
        'type',
        'superficie',
        'capacite',
        'etage',
        'disponible',
        'statut_conformite',
        'description'

    ];

    protected $casts = [
        'disponible' => 'boolean'
    ];

    public function batiment(){
        return $this->belongsTo(Batiment::class);
    }

   public function chambres(){
    return $this->hasMany(Chambre::class, 'id_local');
}



    public function equipements(){
        return $this->hasMany(Equipement::class);
    }

    public function reservations(){
        return $this->hasMany(Reservation::class);
    }

    public function affectations(){
        return $this->hasMany(Affectation::class);
    }

    public function demandeAffectations(){
        return $this->hasMany(DemandeAffectation::class);
    }

    public function reclamations(){
        return $this->hasMany(Reclamation::class);
    }

    public function cartographieElement(){
        return $this->hasMany(CartographieElement::class);
    }

    public function getAffectationActive(){
        return $this->affectations()
        ->where('satut', 'Active')
        ->whereDate('dateFin','>=', now())
        ->first();
    }

    public function estOccupe(){
        return $this->getAffectationActive() !== null;
    }

   public function verifierDisponibilite($dateDebut, $dateFin)
    {
        // Vérifier s'il y a des affectations actives qui chevaucheraient
        $affectationsChevauchantes = $this->affectations()
            ->where('statut', 'Active')
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('dateDebut', [$dateDebut, $dateFin])
                    ->orWhereBetween('dateFin', [$dateDebut, $dateFin])
                    ->orWhere(function ($q) use ($dateDebut, $dateFin) {
                        $q->where('dateDebut', '<=', $dateDebut)
                          ->where('dateFin', '>=', $dateFin);
                    });
            })
            ->count();
        
        // Vérifier s'il y a des réservations qui chevaucheraient
        $reservationsChevauchantes = $this->reservations()
            ->where('statut', 'Approuvée')
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('dateDebut', [$dateDebut, $dateFin])
                    ->orWhereBetween('dateFin', [$dateDebut, $dateFin])
                    ->orWhere(function ($q) use ($dateDebut, $dateFin) {
                        $q->where('dateDebut', '<=', $dateDebut)
                          ->where('dateFin', '>=', $dateFin);
                    });
            })
            ->count();
        
        return $affectationsChevauchantes == 0 && $reservationsChevauchantes == 0;
    }
    
    // Vérifie la conformité QHSE du local
    public function verifierConformite()
    {
        $enquete = EnqueteQHSE::whereHas('demandeAffectation', function ($query) {
            $query->where('id_local', $this->id);
        })
        ->where('statut', 'Terminee')
        ->orderBy('dateFin', 'desc')
        ->first();
        
        return $enquete ? $enquete->conforme : false;
    }
}


