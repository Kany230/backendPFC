<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MaintenanceController extends Controller
{
    protected $rules = [
            'id_equipement' => 'required|exists:equipements,id',
            'type' => 'required|in:Préventive,Corrective,Urgente',
            'description' => 'nullable|string',
            'priorite' => 'nullable|in:Faible,Normale,Élevée,Urgente',
            'dateSignalement' => 'required|date',
        'dateDebut' => 'nullable|date|after_or_equal:dateSignalement',
        'dateFin' => 'nullable|date|after_or_equal:dateDebut',
            'statut' => 'nullable|in:Signalée,Programmée,En cours,Terminée,Annulée',
            'cout' => 'nullable|numeric',
        'remarques' => 'nullable|string'
    ];

    public function index(Request $request)
    {
        $query = Maintenance::with('equipement');

        // Filtres
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%$search%")
                  ->orWhere('type', 'like', "%$search%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }

        if ($priorite = $request->input('priorite')) {
            $query->where('priorite', $priorite);
        }

        // Tri
        $orderBy = $request->input('orderBy', 'dateSignalement');
        $orderDir = $request->input('orderDir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = $request->input('perPage', 15);
        $maintenances = $query->paginate($perPage);
        
        return $this->jsonResponse($maintenances, 'Liste des maintenances récupérée avec succès');
    }

    public function show($id)
    {
        $maintenance = Maintenance::with('equipement')->findOrFail($id);
        return $this->jsonResponse($maintenance);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules);
        $maintenance = Maintenance::create($validated);

        return $this->jsonResponse(
            $maintenance->load('equipement'),
            'Maintenance créée avec succès',
            'success',
            201
        );
    }

    public function update(Request $request, $id)
    {
        $maintenance = Maintenance::findOrFail($id);
                                               
        $validated = $request->validate([
            'id_equipement' => 'sometimes|exists:equipements,id',
            'type' => 'sometimes|in:Préventive,Corrective,Urgente',
            'description' => 'nullable|string',
            'priorite' => 'nullable|in:Faible,Normale,Élevée,Urgente',
            'dateSignalement' => 'sometimes|date',
            'dateDebut' => 'nullable|date|after_or_equal:dateSignalement',
            'dateFin' => 'nullable|date|after_or_equal:dateDebut',
            'statut' => 'nullable|in:Signalée,Programmée,En cours,Terminée,Annulée',
            'cout' => 'nullable|numeric',
            'remarques' => 'nullable|string'
        ]);

        $maintenance->update($validated);

        return $this->jsonResponse(
            $maintenance->fresh('equipement'),
            'Maintenance mise à jour avec succès'
        );
    }

    public function destroy($id)
    {
        $maintenance = Maintenance::findOrFail($id);
        $maintenance->delete();

        return $this->jsonResponse(null, 'Maintenance supprimée avec succès');
    }

    public function programmer(Request $request, $id)
    {
        $maintenance = Maintenance::findOrFail($id);
                                               
        $validated = $request->validate([
            'dateDebut' => 'required|date|after_or_equal:today'
        ]);

        $maintenance->update([
            'dateDebut' => $validated['dateDebut'],
            'statut' => 'Programmée'
        ]);

        return $this->jsonResponse($maintenance, 'Maintenance programmée avec succès');
    }

    public function demarrer(Request $request, Maintenance $maintenance)
    {
        if ($maintenance->statut !== 'Programmée') {
            return $this->jsonResponse(
                null,
                'La maintenance doit être programmée avant de pouvoir être démarrée',
                'error',
                400
            );
        }

        $maintenance->update([
            'statut' => 'En cours',
            'dateDebut' => now()
        ]);

        return $this->jsonResponse($maintenance, 'Maintenance démarrée avec succès');
    }

    public function terminer(Request $request, Maintenance $maintenance)
    {
        $validated = $request->validate([
            'rapport' => 'required|string',
            'cout' => 'required|numeric'
        ]);
                                        
        if ($maintenance->statut !== 'En cours') {
            return $this->jsonResponse(
                null,
                'La maintenance doit être en cours avant de pouvoir être terminée',
                'error',
                400
            );
        }

        $maintenance->update([
            'statut' => 'Terminée',
            'dateFin' => now(),
            'remarques' => $validated['rapport'],
            'cout' => $validated['cout']
        ]);

        return $this->jsonResponse($maintenance, 'Maintenance terminée avec succès');
        }

    public function annuler(Request $request, Maintenance $maintenance)
    {
        $validated = $request->validate([
            'raison' => 'required|string'
        ]);

        if ($maintenance->statut === 'Terminée') {
            return $this->jsonResponse(
                null,
                'Impossible d\'annuler une maintenance terminée',
                'error',
                400
            );
        }

        $maintenance->update([
            'statut' => 'Annulée',
            'remarques' => $validated['raison']
        ]);

        return $this->jsonResponse($maintenance, 'Maintenance annulée avec succès');
    }

    protected function jsonResponse($data, $message = '', $status = 'success', $code = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
