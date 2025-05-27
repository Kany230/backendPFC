<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id_equipement',
        'type',
        'description', 
        'priorite',
        'date_signalement',
        'date_debut',
        'date_fin',
        'statut',
        'cout',
        'remarques'
    ];
    protected $dates = [
        'date_signalement', 'date_debut', 'date_fin'
    ];
    
    public function equipement()
    {
        return $this->belongsTo(Equipement::class);
    }
    
    public function alertes()
    {
        return $this->hasMany(Alerte::class, 'source_id')
            ->where('type', 'Maintenance');
    }
    
    
    public function cloturerMaintenance($cout, $remarques)
    {
        $this->update([
            'date_fin' => now(),
            'statut' => 'Terminee',
            'cout' => $cout,
            'remarques' => $remarques
        ]);
        
        
        $this->equipement->update(['etat' => 'Bon']);
        
      
        $this->alertes()->update(['vue' => true]);
    }
    
    
    public function programmerMaintenance($dateDebut)
    {
        $this->update([
            'statut' => 'Programmee',
            'date_debut' => $dateDebut
        ]);
    }
}

