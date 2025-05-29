<?php

namespace App\Http\Controllers;

use App\Models\CartographieElement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartographieElementController extends Controller
{
    public function index(Request $request)
    {
        $query = CartographieElement::query();

        // Filtrer par bâtiment si l'ID est fourni
        if ($request->has('batiment_id')) {
            $query->where('batiment_id', $request->batiment_id);
        }

        $elements = $query->get();
        return response()->json($elements);
    }

    public function show(CartographieElement $element)
    {
        return response()->json($element);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), CartographieElement::$rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
    }

        $element = CartographieElement::create($request->all());
        return response()->json($element, 201);
    }

    public function update(Request $request, CartographieElement $element)
    {
        $validator = Validator::make($request->all(), CartographieElement::$rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $element->update($request->all());
        return response()->json($element);
    }

    public function destroy(CartographieElement $element)
    {
        $element->delete();
        return response()->json(null, 204);
    }
 
    public function updatePosition(Request $request, CartographieElement $element)
    {
        $validator = Validator::make($request->all(), [
            'coordonnees_x' => 'required|numeric',
            'coordonnees_y' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $element->updatePosition($request->coordonnees_x, $request->coordonnees_y);
        return response()->json($element);
    }

    public function updateDimensions(Request $request, CartographieElement $element)
    {
        $validator = Validator::make($request->all(), [
            'largeur' => 'required|numeric|min:0',
            'hauteur' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $element->updateDimensions($request->largeur, $request->hauteur);
        return response()->json($element);
    }

    public function updateRotation(Request $request, CartographieElement $element)
    {
        $validator = Validator::make($request->all(), [
            'rotation' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $element->updateRotation($request->rotation);
        return response()->json($element);
    }
}
