<?php

namespace App\Http\Controllers;

use App\Models\CartographieElement;
use App\Models\Calque;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartographieController extends Controller
{
    protected $rules = [
        'id_calque' => 'required|exists:calques,id',
        'type' => 'required|in:Point,Ligne,Polygone,Texte',
        'coordonnees' => 'required|json',
        'proprietes' => 'nullable|json',
        'style' => 'nullable|json',
        'ordre' => 'nullable|integer',
        'visible' => 'boolean'
    ];

    public function index(Request $request)
    {
        $query = CartographieElement::with(['calque']);

        // Filtres
        if ($calqueId = $request->input('id_calque')) {
            $query->where('id_calque', $calqueId);
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($visible = $request->input('visible')) {
            $query->where('visible', $visible);
        }

        // Tri
        $orderBy = $request->input('orderBy', 'ordre');
        $orderDir = $request->input('orderDir', 'asc');
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = $request->input('perPage', 50);
        $elements = $query->paginate($perPage);

        return $this->jsonResponse($elements, 'Liste des éléments cartographiques récupérée avec succès');
    }

    public function show($id)
    {
        $element = CartographieElement::with(['calque'])->findOrFail($id);
        return $this->jsonResponse($element);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules);
        
        // Vérifier que les coordonnées sont valides
        $coordonnees = json_decode($validated['coordonnees']);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse(
                null,
                'Les coordonnées ne sont pas au format JSON valide',
                'error',
                400
            );
        }

        // Vérifier les propriétés si présentes
        if (isset($validated['proprietes'])) {
            $proprietes = json_decode($validated['proprietes']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->jsonResponse(
                    null,
                    'Les propriétés ne sont pas au format JSON valide',
                    'error',
                    400
                );
            }
        }

        // Vérifier le style si présent
        if (isset($validated['style'])) {
            $style = json_decode($validated['style']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->jsonResponse(
                    null,
                    'Le style n\'est pas au format JSON valide',
                    'error',
                    400
                );
            }
        }

        $element = CartographieElement::create($validated);

        return $this->jsonResponse(
            $element->load('calque'),
            'Élément cartographique créé avec succès',
            'success',
            201
        );
    }

    public function update(Request $request, $id)
    {
        $element = CartographieElement::findOrFail($id);

        $validated = $request->validate([
            'id_calque' => 'sometimes|exists:calques,id',
            'type' => 'sometimes|in:Point,Ligne,Polygone,Texte',
            'coordonnees' => 'sometimes|json',
            'proprietes' => 'nullable|json',
            'style' => 'nullable|json',
            'ordre' => 'nullable|integer',
            'visible' => 'boolean'
        ]);

        // Vérifications JSON comme dans store()
        if (isset($validated['coordonnees'])) {
            $coordonnees = json_decode($validated['coordonnees']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->jsonResponse(
                    null,
                    'Les coordonnées ne sont pas au format JSON valide',
                    'error',
                    400
                );
            }
        }

        if (isset($validated['proprietes'])) {
            $proprietes = json_decode($validated['proprietes']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->jsonResponse(
                    null,
                    'Les propriétés ne sont pas au format JSON valide',
                    'error',
                    400
                );
            }
        }

        if (isset($validated['style'])) {
            $style = json_decode($validated['style']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->jsonResponse(
                    null,
                    'Le style n\'est pas au format JSON valide',
                    'error',
                    400
                );
            }
        }

        $element->update($validated);

        return $this->jsonResponse(
            $element->fresh('calque'),
            'Élément cartographique mis à jour avec succès'
        );
    }

    public function destroy($id)
    {
        $element = CartographieElement::findOrFail($id);
        $element->delete();

        return $this->jsonResponse(null, 'Élément cartographique supprimé avec succès');
    }

    public function updateOrdre(Request $request)
    {
        $validated = $request->validate([
            'elements' => 'required|array',
            'elements.*.id' => 'required|exists:cartographie_elements,id',
            'elements.*.ordre' => 'required|integer|min:0'
        ]);

        foreach ($validated['elements'] as $element) {
            CartographieElement::where('id', $element['id'])
                ->update(['ordre' => $element['ordre']]);
        }

        return $this->jsonResponse(null, 'Ordre des éléments mis à jour avec succès');
    }

    public function toggleVisibilite($id)
    {
        $element = CartographieElement::findOrFail($id);
        $element->visible = !$element->visible;
        $element->save();

        return $this->jsonResponse(
            $element->fresh('calque'),
            'Visibilité de l\'élément mise à jour avec succès'
        );
    }

    public function getParCalque($calqueId)
    {
        $elements = CartographieElement::where('id_calque', $calqueId)
            ->orderBy('ordre')
            ->get();

        return $this->jsonResponse($elements);
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