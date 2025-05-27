<?php

namespace App\Http\Controllers;

use App\Models\Reclamation;
use Illuminate\Http\Request;

class ReclamationController extends Controller
{
    public function index(){

        $reclamations = Reclamation::with(['utilisateur', 'local'])->get();

        return response()->json([
            'status' => 'success',
            'data' => $reclamations
        ]);
    }

    public function show($id){

        $reclamation = Reclamation::with(['utilisateur', 'local', 'maintenances'])->findOrFail($id);

        if(!$reclamation){
            return response()->json([
                'status' => 'error',
                'message' => 'Reclamation non trouve'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $reclamation
        ]);
    }

    public function store(Request $request){

        $request->validate([
            'id_utilisateur' => 'required|exists:users,id',
            'id_local' => 'required|exists:locals,id',
            'objet' => 'required|string|max:255',
            'description' => 'required|string',
            'priorite' => 'required|in:Faible,Normale,Élevée,Urgente',
            'statut' => 'nullable|in:Ouverte,Assignée,En cours,Résolue,Fermée',
            'dateCreation' => 'nullable|date',
        ]);

        $reclamation = Reclamation::create(array_merge(
            $request->all(),
            ['dateCreation' => $request->dateCreation ?? now()]
        ));

        return response()->json([
            'message' => 'Reclamation creee',
            'data' => $reclamation
        ], 201);
    }

    public function update(Request $request, $id){

        $reclamation = Reclamation::findOrFail($id);

        if(!$reclamation){
            return response()->json([
                'status' => 'error',
                'message' => 'Reclamation non trouve'
            ], 404);
        }

        $request->validate([
            'id_local' => 'sometimes|exists:locals,id',
            'objet' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'statut' => 'sometimes|in:Ouverte,Assignée,En cours,Résolue,Fermée',
            'priorite' => 'sometimes|inFaible,Normale,Élevée,Urgente',
            'solution' => 'nullable|string',
            'satisfaction' => 'nullable|integer|min:1|max:5',
            'dateResolution' => 'nullable|date',
        ]);

        $reclamation->update($request->all());

        return response()->json([
            'message' => 'Reclamation mise à jour',
            'data' => $reclamation
        ]);
    }

    public function destroy($id){

        $reclamation = Reclamation::findOrFail($id);

        if(!$reclamation){
            return response()->json([
                'status' => 'error',
                'message' => 'Reclamation non trouve'
            ], 404);
        }

        $reclamation->delete();

        return response()->json([
            'message' => 'Reclamation supprimee'
        ]);
    }

    public function assignerAgent(Request $request, $id){

        $request->validate([
            'agent_id' => 'required|exists:users,id'
        ]);

        $reclamation = Reclamation::findOrFail($id);
        
        if(!$reclamation){
            return response()->json([
                'status' => 'error',
                'message' => 'Reclamation non trouve'
            ], 404);
        }
        
        $reclamation->assignerAgent($request->agent_id);

        return response()->json([
            'message' => 'Reclamation assignee à l\'agent avec succes',
            'data' => $reclamation
        ]);
    }

    public function resoudre(Request $request, $id){

        $request->validate([
            'solution' => 'required|string'
        ]);

        $reclamation = Reclamation::findOrFail($id);

        if(!$reclamation){
            return response()->json([
                'status' => 'error',
                'message' => 'Reclamation non trouve'
            ], 404);
        }

        $reclamation->resoudre($request->solution);

        return response()->json([
            'message' => 'Reclamation resolue avec succes',
            'data' => $reclamation
        ]);
    }

    public function evaluerSatisfaction(Request $request, $id){

        $request->validate([
            'note' => 'required|integer|min:1|max:5'
        ]);

        $reclamation = Reclamation::findOrFail($id);

        if(!$reclamation){
            return response()->json([
                'status' => 'error',
                'message' => 'Reclamation non trouve'
            ], 404);
        }


        $reclamation->evaluerSatisfaction($request->note);

        return response()->json([
            'message' => 'Message enregistree',
            'data' => $reclamation
        ]);
    }

    public function creerMaintenance($id){

        $reclamation = Reclamation::findOrFail($id);

        if(!$reclamation){
            return response()->json([
                'status' => 'error',
                'message' => 'Reclamation non trouve'
            ], 404);
        }

        try {
            $maintenance = $reclamation->creerMaintenance();
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la creation de la maintenance: ' . $e->getMessage()
            ], 400);
        }

        return response()->json([
            'message' => 'Maintenance creee avec succes',
            'data' => $maintenance
        ]);
    }
}
