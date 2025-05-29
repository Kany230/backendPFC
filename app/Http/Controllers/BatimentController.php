<?php

namespace App\Http\Controllers;

use App\Models\Batiment;
use Illuminate\Http\Request;

class BatimentController extends Controller
{
    protected $rules = [
        'nom' => 'required|string',
        'adresse' => 'required|string',
        'superficie' => 'nullable|numeric',
        'description' => 'nullable|string',
        'dateConstruction' => 'nullable|date',
        'localisation_lat' => 'nullable|numeric',
        'localisation_lng' => 'nullable|numeric'
    ];

    public function index(Request $request)
    {
        $query = Batiment::query();

        // Filtres
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%$search%")
                  ->orWhere('adresse', 'like', "%$search%");
            });
        }

        // Tri
        $orderBy = $request->input('orderBy', 'nom');
        $orderDir = $request->input('orderDir', 'asc');
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = $request->input('perPage', 15);
        $batiments = $query->paginate($perPage);

        return $this->jsonResponse($batiments, 'Liste des bâtiments récupérée avec succès');
    }

    public function show($id)
    {
        $batiment = Batiment::with(['locaux.chambres'])->findOrFail($id);
        return $this->jsonResponse($batiment);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules);
        $batiment = Batiment::create($validated);

        return $this->jsonResponse(
            $batiment,
            'Bâtiment créé avec succès',
            'success',
            201
        );
    }

    public function update(Request $request, $id)
    {
        $batiment = Batiment::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string',
            'adresse' => 'sometimes|string',
            'superficie' => 'nullable|numeric',
            'description' => 'nullable|string',
            'dateConstruction' => 'nullable|date',
            'localisation_lat' => 'nullable|numeric',
            'localisation_lng' => 'nullable|numeric'
        ]);

        $batiment->update($validated);

        return $this->jsonResponse(
            $batiment->fresh(),
            'Bâtiment mis à jour avec succès'
        );
    }

    public function destroy($id)
    {
        $batiment = Batiment::findOrFail($id);
        $batiment->delete();

        return $this->jsonResponse(null, 'Bâtiment supprimé avec succès');
    }

    public function occupants($id)
    {
        $batiment = Batiment::findOrFail($id);
        $occupants = $batiment->getOccupants();

        return $this->jsonResponse($occupants, 'Liste des occupants récupérée avec succès');
    }

    public function getRevenus($id)
    {
        $batiment = Batiment::findOrFail($id);
        $revenus = $batiment->calculerRevenus();

        return $this->jsonResponse($revenus, 'Revenus du bâtiment calculés avec succès');
        }

    public function revenusParAnnee($id)
    {
        $batiment = Batiment::findOrFail($id);
        $revenus = $batiment->calculerRevenusParAnnee();

        return $this->jsonResponse($revenus, 'Revenus annuels calculés avec succès');
    }

    public function revenusParMois($id, $annee = null)
    {
        $batiment = Batiment::findOrFail($id);
        $revenus = $batiment->calculerRevenusParMois($annee);

        return $this->jsonResponse($revenus, 'Revenus mensuels calculés avec succès');
    }

    public function getStatistiques($id)
    {
        $batiment = Batiment::findOrFail($id);
        $stats = $batiment->getStatistiques();

        return $this->jsonResponse($stats, 'Statistiques du bâtiment récupérées avec succès');
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
