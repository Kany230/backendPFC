<?php

namespace App\Http\Controllers;

use App\Models\Local;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocalController extends Controller
{
    protected $rules = [
        'nom' => 'required|string|max:255',
        'id_batiment' => 'required|exists:batiments,id',
        'type' => 'required|string',
        'superficie' => 'nullable|numeric',
        'capacite' => 'nullable|integer',
        'etage' => 'nullable|string',
        'disponible' => 'boolean',
        'statut_conformite' => 'nullable|string',
        'description' => 'nullable|string'
    ];

    public function index(Request $request)
    {
        $query = Local::with(['batiment', 'chambres']);

        // Filtres
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%$search%")
                  ->orWhere('type', 'like', "%$search%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($batimentId = $request->input('id_batiment')) {
            $query->where('id_batiment', $batimentId);
        }

        // Tri
        $orderBy = $request->input('orderBy', 'nom');
        $orderDir = $request->input('orderDir', 'asc');
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = $request->input('perPage', 15);
        $locaux = $query->paginate($perPage);

        return $this->jsonResponse($locaux, 'Liste des locaux récupérée avec succès');
    }

    public function show($id)
    {
        $local = Local::with([
            'batiment',
            'chambres',
            'equipements',
            'reservations',
            'affectations',
            'demandeAffectations',
            'reclamations',
            'cartographieElement'
        ])->findOrFail($id);

        return $this->jsonResponse($local);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules);
        $local = Local::create($validated);

        return $this->jsonResponse(
            $local->load('batiment'),
            'Local créé avec succès',
            'success',
            201
        );
    } 

    public function update(Request $request, $id)
    {
        $local = Local::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'id_batiment' => 'sometimes|exists:batiments,id',
            'type' => 'sometimes|string',
            'superficie' => 'nullable|numeric',
            'capacite' => 'nullable|integer',
            'etage' => 'nullable|string',
            'disponible' => 'boolean',
            'statut_conformite' => 'nullable|string',
            'description' => 'nullable|string'
        ]);

        $local->update($validated);

        return $this->jsonResponse(
            $local->fresh('batiment'),
            'Local mis à jour avec succès'
        );
    }

    public function destroy($id)
    {
        $local = Local::findOrFail($id);
        $local->delete();

        return $this->jsonResponse(null, 'Local supprimé avec succès');
    }

    public function estOccupe($id)
    {
        $local = Local::findOrFail($id);
        return $this->jsonResponse(['est_occupe' => $local->estOccupe()]);
    }

    public function verifierDisponibilite(Request $request, $id)
    {
        $validated = $request->validate([
            'dateDebut' => 'required|date',
            'dateFin' => 'required|date|after:dateDebut'
        ]);

        $local = Local::findOrFail($id);

        $disponible = $local->verifierDisponibilite(
            $validated['dateDebut'],
            $validated['dateFin']
        );

        return $this->jsonResponse(['disponible' => $disponible]);
    }

    public function getAffectationActive($id)
    {
        $local = Local::findOrFail($id);
        $affectation = $local->getAffectationActive();

        return $this->jsonResponse($affectation);
    }

    public function verifierConformite($id)
    {
        $local = Local::findOrFail($id);
        return $this->jsonResponse(['conforme' => $local->verifierConformite()]);
    }

    public function chambresDuPavillon($id)
    {
        $local = Local::findOrFail($id);

        if ($local->type !== 'Pavillon') {
            return $this->jsonResponse(
                null,
                'Ce local n\'est pas un pavillon',
                'error',
                400
            );
        }

        return $this->jsonResponse($local->chambres);
}   

    public function locauxParType($type)
    {
        $locaux = Local::where('type', $type)
            ->with('batiment')
            ->paginate(15);

        return $this->jsonResponse($locaux);
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
