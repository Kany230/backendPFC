<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id_local',
        'nom',
        'type',
        'numeroSerie',
        'dateAcquisition',
        'dateFinGarantie',
        'etat',
        'valeur',
        'description'
    ];

    protected $dates = [
        'dateAcquisition', 'dateFinGarantie'
    ];

    public function local(){
        return $this->belongsTo(Local::class);
    }

    public function maintenances(){
        return $this->hasMany(Maintenance::class);
    }

    public function getHistoriqueMaintenance()  {
        return $this->maintenances()->orderBy('dateSignalement', 'desc')->get();
    }

    public function necessiteMaintenance(){

        if($this->etat != 'Bon'){
            return true;
        }

        $dernierMaintenance = $this->maintenances()
        ->where('statut', 'Terminee')
        ->orderBy('date_fin', 'desc')
        ->first();

        if(!$dernierMaintenance){
            return true;
        }

        return $dernierMaintenance->date_fin->diffInMonths(now()) >= 6;
    }
}
