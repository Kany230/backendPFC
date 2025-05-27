<?php

namespace App\Http\Controllers;

use App\Models\CartographieElement;
use Illuminate\Http\Request;

class CartographieElementController extends Controller
{
    public function index(){

        $data = CartographieElement::all();
        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function store(Request $request){

        $data = $request->validate([
            'id_batiment' => 'required|exists:batiments,id',
            'id_local' => 'nullable|exists:locals,id',
            'type' => 'required|string',
            'coordonnees_x' => 'required|numeric',
            'coordonnees_y' => 'required|numeric',
            'largeur' => 'required|numeric',
            'hauteur' => 'required|numeric',
            'rotation' => 'required|numeric',
            'couleur' => 'nullable|string',
            'label' => 'nullable|string',
            'details' => 'nullable|array'
        ]);

        $element = CartographieElement::create($data);
        return response()->json([
            'status' => 'success',
            'data' => $element
        ], 201);
    }

    public function show($id){
        $element = CartographieElement::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $element
        ]);
    }

    public function update(Request $request, $id){

        $element = CartographieElement::findOrFail($id);

        $data = $request->validate([
            'coordonnees_x' => 'nullable|numeric',
            'coordonnees_y' => 'nullable|numeric',
            'largeur' => 'nullable|numeric',
            'hauteur' => 'nullable|numeric',
            'rotation' => 'nullable|numeric',
            'couleur' => 'nullable|string',
            'label' => 'nullable|string',
            'details' => 'nullable|array'
        ]);

        $element->update($data);
        return response()->json([
            'status' => 'success',
            'data' => $element
        ]);
    }

    public function destroy($id){

        $element = CartographieElement::findOrFail($id);

        if(!$element){
            return response()->json([
                'status' => 'error',
                'message' => 'Element non trouve'
            ], 404);
        }
        $element->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Element supprimer'
        ]);
    }
 
     // Mise a jour de la position (coordonnées)
    public function updatePosition(Request $request, $id)
    {
        $request->validate([
            'coordonnees_x' => 'required|numeric',
            'coordonnees_y' => 'required|numeric'
        ]);

        $element = CartographieElement::findOrFail($id);
        $element->updatePosition($request->coordonnees_x, $request->coordonnees_y);

        return response()->json([
            'status' => 'success',
            'message' => 'Position mis a jour', 
            'element' => $element
        ]);
    }

    // Mise a jour des dimensions
    public function updateDimensions(Request $request, $id)
    {
        $request->validate([
            'largeur' => 'required|numeric',
            'hauteur' => 'required|numeric'
        ]);

        $element = CartographieElement::findOrFail($id);
        $element->updateDimensions($request->largeur, $request->hauteur);

        return response()->json([
            'status' => 'success',
            'message' => 'Dimensions mis a jour', 
            'element' => $element
        ]);
    }

    // Mise à jour de la rotation
    public function updateRotation(Request $request, $id)
    {
        $request->validate([
            'rotation' => 'required|numeric'
        ]);

        $element = CartographieElement::findOrFail($id);
        $element->updateRotation($request->rotation);

        return response()->json([
            'status' => 'success',
            'message' => 'Rotation mise à jour avec succès.',
            'element' => $element
        ]);
    }
}
