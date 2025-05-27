<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alerte extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'type', 
        'source_id', 
        'message', 
        'dateCreation', 
        'dateEcheance', 
        'priorite', 
        'vue'
    ];
    
    protected $dates = [
        'dateCreation', 'dateEcheance'
    ];
    
    protected $casts = [
        'vue' => 'boolean'
    ];
    
    // Relation polymorphique pour faciliter l'accès à la source
    public function source()
    {
        if ($this->type === 'Maintenance') {
            return $this->belongsTo(Maintenance::class, 'source_id');
        } elseif ($this->type === 'Contrat') {
            return $this->belongsTo(Contrat::class, 'source_id');
        } elseif ($this->type === 'Paiement') {
            return $this->belongsTo(Paiement::class, 'source_id');
        }
        
        return null;
    }

    public function utilisateur()
{
    return $this->belongsTo(User::class, 'user_id');
}

    
    // Marquer comme vue
    public function marquerVue()
    {
        $this->update(['vue' => true]);
    }
    
    // Récupérer les alertes actives pour un utilisateur
    public static function getAlertesActives($utilisateurId = null)
    {
        $query = self::where('vue', false)
            ->where('dateEcheance', '<=', now())
            ->orderBy('priorite', 'desc')
            ->orderBy('datEcheance', 'asc');
            
        if ($utilisateurId) {
            // Filtrer les alertes pertinentes pour cet utilisateur
            // Cette logique dépendra des rôles et permissions
        }
        
        return $query->get();
    }
}
