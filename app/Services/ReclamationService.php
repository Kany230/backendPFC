<?php

namespace App\Services;

use App\Models\Reclamation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ReclamationService
{
    private $equipementService;

    public function __construct(EquipementService $equipementService)
    {
        $this->equipementService = $equipementService;
    }

    /**
     * Créer une nouvelle réclamation
     */
    public function creerReclamation(array $data): Reclamation
    {
        DB::beginTransaction();
        try {
            $reclamation = new Reclamation();
            $reclamation->equipement_id = $data['equipement_id'];
            $reclamation->utilisateur_id = $data['utilisateur_id'];
            $reclamation->description = $data['description'];
            $reclamation->priorite = $data['priorite'];
            $reclamation->statut = 'En attente';
            $reclamation->date_creation = Carbon::now();
            $reclamation->save();

            // Mettre à jour l'état de l'équipement
            $this->equipementService->updateEtatEquipement($data['equipement_id'], 'Mauvais');

            DB::commit();
            return $reclamation;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Récupérer les réclamations d'un utilisateur
     */
    public function getReclamationsUtilisateur(int $userId): Collection
    {
        return Reclamation::where('id_utilisateur', $userId)
            ->with(['local', 'agent'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Récupérer les réclamations à traiter
     */
    public function getReclamationsATraiter(): Collection
    {
        return Reclamation::whereIn('statut', ['Ouverte', 'Assignée'])
            ->with(['utilisateur', 'local'])
            ->orderBy('priorite', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Récupérer les réclamations assignées à un technicien
     */
    public function getReclamationsTechnicien(int $technicienId): Collection
    {
        return Reclamation::where('id_agent', $technicienId)
            ->whereIn('statut', ['Assignée', 'En cours'])
            ->with(['utilisateur', 'local'])
            ->orderBy('priorite', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Attribuer une réclamation à un technicien
     */
    public function attribuerReclamation(int $reclamationId, int $technicienId): Reclamation
    {
        $reclamation = Reclamation::findOrFail($reclamationId);
        $technicien = User::findOrFail($technicienId);

        if ($reclamation->statut !== 'Ouverte') {
            throw new \Exception('La réclamation ne peut pas être assignée car elle n\'est pas ouverte');
        }

        $reclamation->update([
            'id_agent' => $technicien->id,
            'statut' => 'Assignée',
            'date_assignation' => now()
        ]);

        return $reclamation->fresh(['utilisateur', 'local', 'agent']);
    }

    /**
     * Mettre à jour le statut d'une réclamation (maintenance)
     */
    public function updateStatutMaintenance(int $reclamationId, array $data): Reclamation
    {
        $reclamation = Reclamation::findOrFail($reclamationId);

        if (!in_array($reclamation->statut, ['Assignée', 'En cours'])) {
            throw new \Exception('La réclamation ne peut pas être mise à jour car elle n\'est pas assignée ou en cours');
        }

        $reclamation->update([
            'statut' => $data['statut'],
            'commentaire_maintenance' => $data['commentaire'],
            'dateResolution' => $data['statut'] === 'Résolue' ? now() : null
        ]);

        return $reclamation->fresh(['utilisateur', 'local', 'agent']);
    }

    /**
     * Valider la résolution d'une réclamation
     */
    public function validerResolution(int $reclamationId, string $commentaire): Reclamation
    {
        $reclamation = Reclamation::findOrFail($reclamationId);

        if ($reclamation->statut !== 'Résolue') {
            throw new \Exception('La réclamation ne peut pas être validée car elle n\'est pas résolue');
        }

        $reclamation->update([
            'statut' => 'Fermée',
            'commentaire_validation' => $commentaire,
            'date_validation' => now()
        ]);

        return $reclamation->fresh(['utilisateur', 'local', 'agent']);
    }

    /**
     * Rejeter une réclamation
     */
    public function rejeterReclamation(int $reclamationId, string $commentaire): Reclamation
    {
        $reclamation = Reclamation::findOrFail($reclamationId);

        if (!in_array($reclamation->statut, ['Ouverte', 'Assignée'])) {
            throw new \Exception('La réclamation ne peut pas être rejetée car elle n\'est pas ouverte ou assignée');
        }

        $reclamation->update([
            'statut' => 'Rejetée',
            'commentaire_rejet' => $commentaire,
            'date_rejet' => now()
        ]);

        return $reclamation->fresh(['utilisateur', 'local', 'agent']);
    }
} 