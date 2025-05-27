<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use Illuminate\Http\Request;

class EquipementController extends Controller
{
    public function index(){
        $equipements = Equipement::with('local')->get();


        return response()->json([
            'status' => 'success',
            'data' => $equipements
        ]);
    }

    public function show($id){

        $equipement = Equipement::with('local', 'maintenances')->findOrFail($id);

        if(!$equipement){
            return response()->json([
                'status' => 'error',
                'message' => 'Equipement demarrer'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $equipement
        ]);
    }

    public function store(Request $request){

        $request->validate([
            'id_local' => 'required|exists:locals,id',
            'nom' => 'required|string|max:255',
            'type' => 'required|in:Mobilier,Électroménager,Informatique,Chauffage,Plomberie,Électricité,Autre',
            'numeroSerie' => 'nullable|string|max:255',
            'dateAcquisition' => 'nullable|date',
            'dateFinGarantie' => 'nullable|date',
            'etat' => 'required|in:Neuf,Bon,Usé,Défaillant,Hors service',
            'valeur' => 'nullable|numeric',
            'description' => 'nullable|string'
        ]);

        $equipement = Equipement::create($request->all());

        return response()->json([
            'message' => 'Équipement cree',
            'data' => $equipement
        ], 201);
    }

    public function update(Request $request, $id){

        $equipement = Equipement::findOrFail($id);

        if(!$equipement){
            return response()->json([
                'status' => 'error',
                'message' => 'Equipement demarrer'
            ], 404);
        }

        $request->validate([
            'id_local' => 'sometimes|required|exists:locals,id',
            'nom' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:Mobilier,Électroménager,Informatique,Chauffage,Plomberie,Électricité,Autre',
            'numeroSerie' => 'nullable|string|max:255',
            'dateAcquisition' => 'nullable|date',
            'dateFinGarantie' => 'nullable|date',
            'etat' => 'sometimes|required|in:Neuf,Bon,Usé,Défaillant,Hors service',
            'valeur' => 'nullable|numeric',
            'description' => 'nullable|string'
        ]);

        $equipement->update($request->all());

        return response()->json([
            'message' => 'Equipement mis a jour',
            'data' => $equipement
        ]);
    }

    public function destroy($id){

        $equipement = Equipement::findOrFail($id);

        if(!$equipement){
            return response()->json([
                'status' => 'error',
                'message' => 'Equipement demarrer'
            ], 404);
        }

        $equipement->delete();

        return response()->json([
            'message' => 'Équipement supprimé avec succès'
        ]);
    }

    public function historiqueMaintenance($id){

        $equipement = Equipement::findOrFail($id);

        if(!$equipement){
            return response()->json([
                'status' => 'error',
                'message' => 'Equipement demarrer'
            ], 404);
        }

        $historique = $equipement->getHistoriqueMaintenance();

        return response()->json([
            'status' => 'success',
            'data' => $historique
        ]);
    }

    public function necessiteMaintenance($id){

        $equipement = Equipement::findOrFail($id);

        if(!$equipement){
            return response()->json([
                'status' => 'error',
                'message' => 'Equipement non trouve'
            ], 404);
        }

        $necessite = $equipement->necessiteMaintenance();

        return response()->json([
            'necessiteMaintenance' => $necessite
        ]);
    }
}
