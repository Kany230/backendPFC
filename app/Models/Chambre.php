<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chambre extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'id_local',
        'numero',
        'superficie',
        'capacite',
        
    ];

    // Relations
    public function local()
    {
        return $this->belongsTo(Local::class, 'id_local');
    }

    public function utilisateurs()
    {
        return $this->belongsTo(User::class, 'id_local');
    }

    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'id_chambre');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'id_chambre');
    }

    // Logique métier
    public function estOccupee()
    {
        return $this->affectations()
            ->where('statut', 'Active')
            ->whereDate('dateFin', '>=', now())
            ->exists();
    }

    public function verifierDisponibilite($dateDebut, $dateFin)
    {
        $affectationsChevauchantes = $this->affectations()
            ->where('statut', 'Active')
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('dateDebut', [$dateDebut, $dateFin])
                    ->orWhereBetween('dateFin', [$dateDebut, $dateFin])
                    ->orWhere(function ($q) use ($dateDebut, $dateFin) {
                        $q->where('dateDebut', '<=', $dateDebut)
                          ->where('dateFin', '>=', $dateFin);
                    });
            })->exists();

        $reservationsChevauchantes = $this->reservations()
            ->where('statut', 'Approuvée')
            ->where(function ($query) use ($dateDebut, $dateFin) {
                $query->whereBetween('dateDebut', [$dateDebut, $dateFin])
                    ->orWhereBetween('dateFin', [$dateDebut, $dateFin])
                    ->orWhere(function ($q) use ($dateDebut, $dateFin) {
                        $q->where('dateDebut', '<=', $dateDebut)
                          ->where('dateFin', '>=', $dateFin);
                    });
            })->exists();

        return !$affectationsChevauchantes && !$reservationsChevauchantes;
    }
}
