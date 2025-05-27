<?php

namespace App\Models;

use App\Notifications\ReservationAnnulee;
use App\Notifications\ReservationApprouvee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
  use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'id_local', 
        'id_utilisateur', 
        'dateDebut', 
        'dateFin',
        'statut', 
        'motif', 
        'remarques'
    ];
    
    protected $dates = [
        'dateDebut', 'dateFin'
    ];
    
    public function local()
    {
        return $this->belongsTo(Local::class);
    }
    
    public function utilisateur()
    {
        return $this->belongsTo(User::class);
    }
    
    // Approuver une réservation
    public function approuver()
    {
        $this->update(['statut' => 'Approuvee']);
        
        // Créer une notification pour l'utilisateur
        $this->utilisateur->notify(new ReservationApprouvee($this));
    }
    
    // Annuler une réservation
    public function annuler($raison = null)
    {
        $this->update([
            'statut' => 'Annulée',
            'remarques' => $raison ?? $this->remarques
        ]);
        
        // Créer une notification pour l'utilisateur
        $this->utilisateur->notify(new ReservationAnnulee($this));
    }
    
    // Vérifier si la réservation chevauche d'autres réservations
    public function verifierChevauchement()
    {
        return $this->local->reservations()
            ->where('id', '!=', $this->id)
            ->where('statut', 'Approuvée')
            ->where(function ($query) {
                $query->whereBetween('date_debut', [$this->date_debut, $this->date_fin])
                    ->orWhereBetween('date_fin', [$this->date_debut, $this->date_fin])
                    ->orWhere(function ($q) {
                        $q->where('date_debut', '<=', $this->date_debut)
                          ->where('date_fin', '>=', $this->date_fin);
                    });
            })
            ->exists();
    }
}

