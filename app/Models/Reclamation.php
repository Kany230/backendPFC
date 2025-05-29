<?php

namespace App\Models;

use App\Notifications\ReclamationAssignee;
use App\Notifications\ReclamationResolue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reclamation extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'id_utilisateur', 
        'id_local', 
        'objet', 
        'description',
        'dateCreation', 
        'statut', 
        'priorite', 
        'dateResolution',
        'solution', 
        'satisfaction'
    ];
    
    protected $dates = [
        'date_creation', 'date_resolution'
    ];
    
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'id_utlisateur');
    }
    
    public function local()
    {
        return $this->belongsTo(Local::class);
    }
    
  
    
    // Assigner une réclamation à un agent
    public function assignerAgent($agentId)
    {
        $this->update([
            'id_agent' => $agentId,
            'statut' => 'Assignée'
        ]);
        
        // Notifier l'agent
        $agent = User::find($agentId);
        $agent->notify(new ReclamationAssignee($this));
    }
    
    // Résoudre une réclamation
    public function resoudre($solution)
    {
        $this->update([
            'statut' => 'Résolue',
            'date_resolution' => now(),
            'solution' => $solution
        ]);
        
        // Notifier l'utilisateur
        $this->utilisateur->notify(new ReclamationResolue($this));
    }
    
    // Évaluer la satisfaction
    public function evaluerSatisfaction($note)
    {
        $this->update([
            'satisfaction' => $note
        ]);
    }
    
    // Si la réclamation nécessite une maintenance
    public function creerMaintenance()
    {
        // Extraire l'équipement concerné du texte de la réclamation
        // Pour simplifier, on suppose qu'un agent a identifié l'équipement
        $equipement = $this->local->equipements()->first();
        
        if (!$equipement) {
            throw new \Exception('Aucun équipement associé à ce local');
        }
        
        $maintenance = Maintenance::create([
            'id_equipement' => $equipement->id,
            'type' => 'Corrective',
            'description' => "Maintenance suite à réclamation: {$this->objet}",
            'priorite' => $this->priorite,
            'date_signalement' => now(),
            'statut' => 'Signalée'
        ]);
        
        return $maintenance;
    }
}
