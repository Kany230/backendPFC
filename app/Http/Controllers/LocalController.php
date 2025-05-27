<?php

namespace App\Http\Controllers;

use App\Models\Local;
use FontLib\Table\Type\loca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocalController extends Controller
{
    public function index(Request $request){

         $locaux = Local::with('batiment', 'chambres')->get();

        return response()->json([
            'status' => 'success',
            'data' => $locaux
        ]);
    }

    public function show($id){

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

        if(!$local){
            return response()->json([
                'status' => 'error',
                'message' => 'Local non trouve'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $local
        ]);
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'id_batiment' => 'required|exists:batiments,id',
            'type' => 'required|string',
            'superficie' => 'nullable|numeric',
            'capacite' => 'nullable|integer',
            'etage' => 'nullable|string',
            'disponible' => 'boolean',
            'statut_conformite' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $local = Local::create($request->all()); 

        return response()->json([
            'status' => 'success',
            'message' => 'Local cree',
            'data' => $local
        ], 201);
    } 

    public function update(Request $request, $id){

        $local = Local::find($id);

        if(!$local){
            return response()->json([
                'status' => 'error', 
                'message' => 'Local non trouve'
            ], 404);
        }

        

        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string|max:255',
            'id_batiment' => 'sometimes|exists:batiments,id',
            'type' => 'sometimes|string',
            'superficie' => 'nullable|numeric',
            'capacite' => 'nullable|integer',
            'etage' => 'nullable|string',
            'disponible' => 'boolean',
            'statut_conformite' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        

        $local->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Local mis a jour',
            'data' => $local
        ]);
    }

    public function destroy($id){

        $local = local::find($id);
        if(!$local){
            return response()->json([
                'status' => 'error',
                'message' => 'Local non trouve'
            ],404);
        }

        $local->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Local supprime avec success'
        ]);
    }

    public function estOccupe($id)
    {
        $local = Local::findOrFail($id);
        return response()->json(['est_occupe' => $local->estOccupe()]);
    }

    public function veifierDisponibiliter(Request $request, $id){

        $request->validate([
            'date-debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut'
        ]);

        $local = Local::find($id);

        if(!$local){
            return response()->json([
                'status' => 'error',
                'message' => 'Local non trouve'
            ], 404);
        }

        $disponible = $local->verifierDisponibilite(
            $request->input('date_debut'),
            $request->input('date_fin')
        );

        return response()->json([
            'status' => 'success',
            'data'=>[
                'disponible' => $disponible
            ]
            ]);
    }

    public function getAffectationActive($id){
        $local = Local::find($id);

        if(!$local){
            return response()->json([
                'status' => 'error',
                'message' => 'Local non trouve'
            ], 404);
        }

        $affectation = $local->getAffectationActive();

        return response()->json([
            'status' => 'success',
            'data' => $affectation
        ]);
    }

    public function verifierConformite($id)
    {
        $local = Local::findOrFail($id);
        $conforme = $local->verifierConformite();

        return response()->json(['conforme' => $conforme]);
    }

    public function chambresDuPavillon($id)
    {
        $local = Local::find($id);

        if (!$local) {
            return response()->json(['error' => 'Local non trouvé'], 404);
        }

        if ($local->type !== 'Pavillon') {
            return response()->json(['error' => 'Ce local n’est pas un pavillon'], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => $local->chambres
        ]);
}   


    public function locauxParType($type)
    {
        // Ex: terrain, cantine, pavillon
        $locaux = Local::where('type', $type)->get();
        return response()->json($locaux);
    }


}
