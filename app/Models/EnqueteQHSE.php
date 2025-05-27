<?php

namespace App\Models;

use App\Notifications\EnqueteQHSECompletee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\PdfService;

class EnqueteQHSE extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'id_demande_affectation', 
        'id_local', 
        'id_agent_qhse',
        'dateDebut',
        'dateFin', 
        'statut', 
        'conclusion',
        'conforme', 
        
    ];
    
    protected $dates = [
        'date_debut', 'date_fin'
    ];
    
    protected $casts = [
        'conforme' => 'boolean'
    ];
    
    public function demandeAffectation()
    {
        return $this->belongsTo(DemandeAffectation::class);
    }
    
    public function local()
    {
        return $this->belongsTo(Local::class);
    }
    
    public function agentQHSE()
    {
        return $this->belongsTo(User::class, 'agent_qhse_id');
    }
    
    public function criteresEvaluation()
    {
        return $this->hasMany(CritereEvaluation::class);
    }
    
    // Démarrer l'enquête
    public function demarrer()
    {
        $this->update([
            'date_debut' => now(),
            'statut' => 'En cours'
        ]);
    }
    
    // Compléter l'enquête
    public function completer($conclusion, $conforme)
    {
        $this->update([
            'date_fin' => now(),
            'statut' => 'Terminee',
            'conclusion' => $conclusion,
            'conforme' => $conforme,
            
        ]);
        
        // Mettre à jour la validation QHSE de la demande d'affectation
        $this->demandeAffectation->update(['validation_qhse' => $conforme]);
        
        // Mettre à jour le statut de conformité du local
        $this->local->update(['statut_conformite' => $conforme ? 'Conforme' : 'Non conforme']);
        
        // Créer une notification pour l'administrateur
        $administrateurs = User::whereHas('roles', function ($query) {
            $query->where('nom', 'Administrateur');
        })->get();
        
        foreach ($administrateurs as $admin) {
            $admin->notify(new EnqueteQHSECompletee($this));
        }
    }
    
    // Générer un rapport d'enquête au format PDF
    public function genererRapport()
    {
        // Voir l'implémentation dans le service PDF
        return app(PdfService::class)->genererRapportEnqueteQHSE($this);
    }
}
