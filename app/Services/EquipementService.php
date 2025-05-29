<?php

namespace App\Services;

use App\Models\Equipement;
use App\Models\Chambre;
use App\Models\Cantine;
use Carbon\Carbon;

class EquipementService
{
    /**
     * Récupérer les équipements d'une chambre
     */
    public function getEquipementsChambre(int $chambreId): array
    {
        $chambre = Chambre::findOrFail($chambreId);
        return $chambre->equipements()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Récupérer les équipements d'une cantine
     */
    public function getEquipementsCantine(int $cantineId): array
    {
        $cantine = Cantine::findOrFail($cantineId);
        return $cantine->equipements()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Créer un nouvel équipement
     */
    public function creerEquipement(array $data): Equipement
    {
        $equipement = new Equipement();
        $equipement->nom = $data['nom'];
        $equipement->description = $data['description'];
        $equipement->type = $data['type'];
        $equipement->etat = 'Bon';
        $equipement->date_installation = Carbon::now();
        $equipement->local_id = $data['local_id'];
        $equipement->local_type = $data['local_type'];
        $equipement->save();

        return $equipement;
    }

    /**
     * Mettre à jour l'état d'un équipement
     */
    public function updateEtatEquipement(int $equipementId, string $etat): Equipement
    {
        $equipement = Equipement::findOrFail($equipementId);
        $equipement->etat = $etat;
        $equipement->save();

        return $equipement;
    }

    /**
     * Enregistrer une maintenance d'équipement
     */
    public function enregistrerMaintenance(int $equipementId): Equipement
    {
        $equipement = Equipement::findOrFail($equipementId);
        $equipement->date_derniere_maintenance = Carbon::now();
        $equipement->etat = 'Bon';
        $equipement->save();

        return $equipement;
    }
} 