<?php

namespace App\Http\Controllers;

use App\Models\Calque;
use App\Models\CartographieElement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CalqueController extends Controller
{
    protected $rules = [
        'nom' => 'required|string|max:255',
        'description' => 'nullable|string',
        'type' => 'required|in:Bâtiments,Locaux,Équipements,Annotations',
        'ordre' => 'nullable|integer',
        'visible' => 'boolean',
        'style_defaut' => 'nullable|json'
    ];

    public function index(Request $request)
    {
        $query = Calque::with(['elements']);

        // Filtres
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            });
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
        $perPage = $request->input('perPage', 15);
        $calques = $query->paginate($perPage);

        return $this->jsonResponse($calques, 'Liste des calques récupérée avec succès');
    }

    public function show($id)
    {
        $calque = Calque::with(['elements'])->findOrFail($id);
        return $this->jsonResponse($calque);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules);

        // Vérifier le style par défaut si présent
        if (isset($validated['style_defaut'])) {
            $style = json_decode($validated['style_defaut']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->jsonResponse(
                    null,
                    'Le style par défaut n\'est pas au format JSON valide',
                    'error',
                    400
                );
            }
        }

        $calque = Calque::create($validated);

        return $this->jsonResponse(
            $calque,
            'Calque créé avec succès',
            'success',
            201
        );
    }

    public function update(Request $request, $id)
    {
        $calque = Calque::findOrFail($id);

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:Bâtiments,Locaux,Équipements,Annotations',
            'ordre' => 'nullable|integer',
            'visible' => 'boolean',
            'style_defaut' => 'nullable|json'
        ]);

        if (isset($validated['style_defaut'])) {
            $style = json_decode($validated['style_defaut']);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->jsonResponse(
                    null,
                    'Le style par défaut n\'est pas au format JSON valide',
                    'error',
                    400
                );
            }
        }

        $calque->update($validated);

        return $this->jsonResponse(
            $calque->fresh(),
            'Calque mis à jour avec succès'
        );
    }

    public function destroy($id)
    {
        $calque = Calque::findOrFail($id);
        $calque->delete();

        return $this->jsonResponse(null, 'Calque supprimé avec succès');
    }

    public function updateOrdre(Request $request)
    {
        $validated = $request->validate([
            'calques' => 'required|array',
            'calques.*.id' => 'required|exists:calques,id',
            'calques.*.ordre' => 'required|integer|min:0'
        ]);

        foreach ($validated['calques'] as $calque) {
            Calque::where('id', $calque['id'])
                ->update(['ordre' => $calque['ordre']]);
        }

        return $this->jsonResponse(null, 'Ordre des calques mis à jour avec succès');
    }

    public function toggleVisibilite($id)
    {
        $calque = Calque::findOrFail($id);
        $calque->visible = !$calque->visible;
        $calque->save();

        return $this->jsonResponse(
            $calque->fresh(),
            'Visibilité du calque mise à jour avec succès'
        );
    }

    public function getElements($id)
    {
        $calque = Calque::findOrFail($id);
        $elements = $calque->elements()
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

    public function addElement(Calque $calque, CartographieElement $element)
    {
        $element->update(['calque_id' => $calque->id]);
        return response()->json(null, 204);
    }

    public function removeElement(Calque $calque, CartographieElement $element)
    {
        if ($element->calque_id === $calque->id) {
            $element->update(['calque_id' => null]);
        }
        return response()->json(null, 204);
    }
} 