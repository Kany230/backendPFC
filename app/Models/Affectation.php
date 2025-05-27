<?php

namespace App\Models;

use App\Notifications\AffectationRenouvelee;
use App\Notifications\AffectationResiliee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Affectation extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'id_demande_affectation', 
        'id_local', 
        'id_utilisateur', 
        'dateDebut', 
        'dateFin', 
        'type', 
        'statut'
    ];
    
    protected $dates = [
        'dateDebut', 'dateFin'
    ];
    
    public function demandeAffectation()
    {
        return $this->belongsTo(DemandeAffectation::class);
    }
    
    public function local()
    {
        return $this->belongsTo(Local::class);
    }
    
    public function utilisateur()
    {
        return $this->belongsTo(User::class);
    }
    
    public function contrat()
    {
        return $this->hasOne(Contrat::class);
    }
    
    // Renouveler une affectation
    public function renouveler($nouvelleDateFin)
    {
        // Vérifier si l'affectation est active
        if ($this->statut !== 'Active' || $this->dateFin < now()) {
            throw new \Exception('Cette affectation ne peut pas être renouvelée');
        }

        $this->update([
            'dateFin' => $nouvelleDateFin
        ]);
        
        // Renouveler aussi le contrat associé
        if ($this->contrat) {
            $this->contrat->renouveler($nouvelleDateFin);
        }
        
        // Notifier l'utilisateur
        $this->utilisateur->notify(new AffectationRenouvelee($this));
    }
    
    // Résilier une affectation
    public function resilier($raison = null)
    {
        $this->update([
            'statut' => 'Résiliee'
        ]);
        
        // Résilier aussi le contrat associé
        if ($this->contrat) {
            $this->contrat->resilier($raison);
        }
        
        // Rendre le local disponible
        $this->local->update(['disponible' => true]);
        
        // Notifier l'utilisateur
        $this->utilisateur->notify(new AffectationResiliee($this, $raison));
    }
}
