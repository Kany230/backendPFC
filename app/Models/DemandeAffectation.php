<?php

namespace App\Models;

use App\Notifications\DemandeAffectationApprouvee;
use App\Notifications\DemandeAffectationRejetee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandeAffectation extends Model
{
   use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'id_local', 
        'id_utilisateur', 
        'dateCreation', 
        'statut',
        'typeOccupation', 
        'description_demande', 
        'avisqhse',
        'validationGestionnaire'
    ];
    
    protected $dates = [
        'dateCreation'
    ];
    
    protected $casts = [
        'avisqhse' => 'boolean',
        'validationGestionnaire' => 'boolean'
    ];
    
    public function local()
    {
        return $this->belongsTo(Local::class);
    }
    
    public function utilisateur()
    {
        return $this->belongsTo(User::class);
    }
    
    public function enqueteQHSE()
    {
        return $this->hasOne(EnqueteQHSE::class);
    }
    
    public function affectation()
    {
        return $this->hasOne(Affectation::class);
    }
    
    // Initier une enquête QHSE
    public function initierEnqueteQHSE($agentQHSEId)
    {
        return EnqueteQHSE::create([
            'id_demande_affectation' => $this->id,
            'id_local' => $this->local_id,
            'agent_qhse_id' => $agentQHSEId,
            'date_debut' => now(),
            'statut' => 'En cours'
        ]);
    }
    
    // Approuver une demande d'affectation
    public function approuver($dateDebut, $dateFin)
    {
        if (!$this->validation_qhse) {
            throw new \Exception('Cette demande n\'est pas encore validee par le service QHSE');
        }
        
        $this->update([
            'validation_admin' => true,
            'statut' => 'Approuvée'
        ]);
        
        // Créer l'affectation
        $affectation = Affectation::create([
            'id_demande_affectation' => $this->id,
            'id_local' => $this->id_local,
            'id_utilisateur' => $this->id_utilisateur,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'type' => $this->type_occupation,
            'statut' => 'Active'
        ]);
        
        // Mettre à jour le statut du local
        $this->local->update(['disponible' => false]);
        
        // Créer une notification pour l'utilisateur
        $this->utilisateur->notify(new DemandeAffectationApprouvee($this));
        
        return $affectation;
    }
    
    // Rejeter une demande d'affectation
    public function rejeter($raison)
    {
        $this->update([
            'statut' => 'Rejetée',
            'description_demande' => $this->description_demande . "\n\nRaison du rejet: " . $raison
        ]);
        
        // Créer une notification pour l'utilisateur
        $this->utilisateur->notify(new DemandeAffectationRejetee($this, $raison));
    }
}

