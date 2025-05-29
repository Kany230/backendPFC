<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Services\EquipementService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EquipementController extends Controller
{
    private $equipementService;

    public function __construct(EquipementService $equipementService)
    {
        $this->equipementService = $equipementService;
    }

    /**
     * Récupérer les équipements d'une chambre
     */
    public function getEquipementsChambre(int $chambreId): JsonResponse
    {
        try {
            $equipements = $this->equipementService->getEquipementsChambre($chambreId);
            return response()->json($equipements);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des équipements'], 500);
        }
    }

    /**
     * Récupérer les équipements d'une cantine
     */
    public function getEquipementsCantine(int $cantineId): JsonResponse
    {
        try {
            $equipements = $this->equipementService->getEquipementsCantine($cantineId);
            return response()->json($equipements);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la récupération des équipements'], 500);
        }
    }

    /**
     * Récupérer un équipement par son ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $equipement = Equipement::findOrFail($id);
            return response()->json($equipement);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Équipement non trouvé'], 404);
        }
    }

    /**
     * Créer un nouvel équipement
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nom' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:Chambre,Cantine',
                'local_id' => 'required|integer',
                'local_type' => 'required|in:Chambre,Cantine'
            ]);

            $equipement = $this->equipementService->creerEquipement($validated);
            return response()->json($equipement, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la création de l\'équipement'], 500);
        }
    }

    /**
     * Mettre à jour l'état d'un équipement
     */
    public function updateEtat(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'etat' => 'required|in:Bon,Moyen,Mauvais'
            ]);

            $equipement = $this->equipementService->updateEtatEquipement($id, $validated['etat']);
            return response()->json($equipement);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la mise à jour de l\'état'], 500);
        }
    }

    /**
     * Enregistrer une maintenance d'équipement
     */
    public function enregistrerMaintenance(int $id): JsonResponse
    {
        try {
            $equipement = $this->equipementService->enregistrerMaintenance($id);
            return response()->json($equipement);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de l\'enregistrement de la maintenance'], 500);
        }
    }
}
