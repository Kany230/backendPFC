<?php

namespace App\Http\Controllers;

use App\Models\Reclamation;
use App\Models\User;
use App\Services\ReclamationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReclamationController extends Controller
{
    protected $rules = [
        'id_local' => 'required|exists:locals,id',
        'objet' => 'required|string|max:255',
        'description' => 'required|string',
        'priorite' => 'required|in:Faible,Normale,Élevée,Urgente',
        'statut' => 'nullable|in:Ouverte,Assignée,En cours,Résolue,Fermée'
    ];

    private $reclamationService;

    public function __construct(ReclamationService $reclamationService)
    {
        $this->reclamationService = $reclamationService;
    }

    public function index(Request $request)
    {
        $query = Reclamation::with(['utilisateur', 'local']);

        // Filtres
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('objet', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhereHas('utilisateur', function($q2) use ($search) {
                      $q2->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%");
                  });
            });
        }

        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }

        if ($priorite = $request->input('priorite')) {
            $query->where('priorite', $priorite);
        }

        // Tri
        $orderBy = $request->input('orderBy', 'created_at');
        $orderDir = $request->input('orderDir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = $request->input('perPage', 15);
        $reclamations = $query->paginate($perPage);

        return $this->jsonResponse($reclamations, 'Liste des réclamations récupérée avec succès');
    }

    public function show($id)
    {
        $reclamation = Reclamation::with(['utilisateur', 'local', 'maintenances'])->findOrFail($id);
        return $this->jsonResponse($reclamation);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules);
        $validated['id_utilisateur'] = auth()->id();
        $validated['statut'] = 'Ouverte';

        $reclamation = Reclamation::create($validated);

        return $this->jsonResponse(
            $reclamation->load(['utilisateur', 'local']),
            'Réclamation créée avec succès',
            'success',
            201
        );
    }

    public function update(Request $request, $id)
    {
        $reclamation = Reclamation::findOrFail($id);

        $validated = $request->validate([
            'id_local' => 'sometimes|exists:locals,id',
            'objet' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'statut' => 'sometimes|in:Ouverte,Assignée,En cours,Résolue,Fermée',
            'priorite' => 'sometimes|in:Faible,Normale,Élevée,Urgente',
            'solution' => 'nullable|string',
            'satisfaction' => 'nullable|integer|min:1|max:5',
            'dateResolution' => 'nullable|date'
        ]);

        $reclamation->update($validated);

        return $this->jsonResponse(
            $reclamation->fresh(['utilisateur', 'local']),
            'Réclamation mise à jour avec succès'
        );
    }

    public function destroy($id)
    {
        $reclamation = Reclamation::findOrFail($id);
        $reclamation->delete();

        return $this->jsonResponse(null, 'Réclamation supprimée avec succès');
    }

    public function assignerAgent(Request $request, $id)
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id'
        ]);

        $reclamation = Reclamation::findOrFail($id);
        
        if ($reclamation->statut !== 'Ouverte') {
            return $this->jsonResponse(
                null,
                'La réclamation ne peut pas être assignée car elle n\'est pas ouverte',
                'error',
                400
            );
        }

        $reclamation->update([
            'id_agent' => $validated['agent_id'],
            'statut' => 'Assignée',
            'date_assignation' => now()
        ]);

        return $this->jsonResponse(
            $reclamation->fresh(['utilisateur', 'local', 'agent']),
            'Réclamation assignée avec succès'
        );
    }

    public function resoudre(Request $request, $id)
    {
        $validated = $request->validate([
            'solution' => 'required|string'
        ]);

        $reclamation = Reclamation::findOrFail($id);

        if (!in_array($reclamation->statut, ['Assignée', 'En cours'])) {
            return $this->jsonResponse(
                null,
                'La réclamation ne peut pas être résolue car elle n\'est pas assignée ou en cours',
                'error',
                400
            );
        }

        $reclamation->update([
            'solution' => $validated['solution'],
            'statut' => 'Résolue',
            'dateResolution' => now()
        ]);

        return $this->jsonResponse(
            $reclamation->fresh(['utilisateur', 'local', 'agent']),
            'Réclamation résolue avec succès'
        );
    }

    public function evaluerSatisfaction(Request $request, $id)
    {
        $validated = $request->validate([
            'satisfaction' => 'required|integer|min:1|max:5',
            'commentaire' => 'nullable|string'
        ]);

        $reclamation = Reclamation::findOrFail($id);

        if ($reclamation->statut !== 'Résolue') {
            return $this->jsonResponse(
                null,
                'La réclamation doit être résolue avant de pouvoir évaluer la satisfaction',
                'error',
                400
            );
        }

        $reclamation->update([
            'satisfaction' => $validated['satisfaction'],
            'commentaire_satisfaction' => $validated['commentaire'],
            'statut' => 'Fermée'
        ]);

        return $this->jsonResponse(
            $reclamation->fresh(['utilisateur', 'local', 'agent']),
            'Satisfaction évaluée avec succès'
        );
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
