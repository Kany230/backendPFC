<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function index(){

        $maintenances = Maintenance::with('equipement')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $maintenances
        ]);
    }

    public function show($id){
                         
        $maintenance = Maintenance::with('equipement')->findOrFail($id);
                                                                   
        if(!$maintenance){
            return response()->json([
                'status' => 'error',
                'message' => 'Maintenance non trouve'
            ], 404);
        }
        
                                                                   
        return response()->json([
            'status' => 'success',
            'data' => $maintenance
        ]);
    }

    public function store(Request $request){
                          
        $request->validate([
            'id_equipement' => 'required|exists:equipements,id',
            'type' => 'required|in:Préventive,Corrective,Urgente',
            'description' => 'nullable|string',
            'priorite' => 'nullable|in:Faible,Normale,Élevée,Urgente',
            'dateSignalement' => 'required|date',
            'dateDebut' => 'nullable|date|after_or_equal:date_signalement',
            'dateFin' => 'nullable|date|after_or_equal:date_debut',
            'statut' => 'nullable|in:Signalée,Programmée,En cours,Terminée,Annulée',
            'cout' => 'nullable|numeric',
            'remarques' => 'nullable|string',
        ]);

        $maintenance = Maintenance::create($request->all());

        return response()->json([
            'message' => 'Maintenance creee',
            'data' => $maintenance
        ], 201);
    }

    public function update(Request $request, $id){
                           
        $maintenance = Maintenance::findOrFail($id);
                                               
        if(!$maintenance){
            return response()->json([
                'status' => 'error',
                'message' => 'Maintenance non trouve'
            ], 404);
        }

        $request->validate([
            'id_equipement' => 'sometimes|exists:equipements,id',
            'type' => 'sometimes|in:Préventive,Corrective,Urgente',
            'description' => 'nullable|string',
            'priorite' => 'nullable|in:Faible,Normale,Élevée,Urgente',
            'dateSignalement' => 'sometimes|date',
            'dateDebut' => 'nullable|date|after_or_equal:date_signalement',
            'dateFin' => 'nullable|date|after_or_equal:date_debut',
            'statut' => 'nullable|in:Signalée,Programmée,En cours,Terminée,Annulée',
            'cout' => 'nullable|numeric',
            'remarques' => 'nullable|string',
        ]);

        $maintenance->update($request->all());

        return response()->json([
            'message' => 'Maintenance mise à jour',
            'data' => $maintenance
        ]);
    }

    public function destroy($id){
                            
        $maintenance = Maintenance::findOrFail($id);
                                               
        if(!$maintenance){
            return response()->json([
                'status' => 'error',
                'message' => 'Maintenance non trouve'
            ], 404);
        }

        $maintenance->delete();

        return response()->json([
            'message' => 'Maintenance supprimée avec succès'
        ]);
    }

    public function programmer(Request $request, $id){
                               
        $maintenance = Maintenance::findOrFail($id);
                                               
        if(!$maintenance){
            return response()->json([
                'status' => 'error',
                'message' => 'Maintenance non trouve'
            ], 404);
        }

        $request->validate([
            'dateDebut' => 'required|date|after_or_equal:today',
        ]);

        $maintenance->programmerMaintenance($request->dateDebut);

        return response()->json([
            'message' => 'Maintenance programmée avec succès',
            'data' => $maintenance
        ]);
    }

    public function cloturer(Request $request, $id){
                             
        $maintenance = Maintenance::findOrFail($id);
                                        
        if(!$maintenance){
            return response()->json([
                'status' => 'error',
                'message' => 'Maintenance non trouve'
            ], 404);
        }

        $request->validate([
            'cout' => 'required|numeric',
            'remarques' => 'nullable|string',
        ]);

        $maintenance->cloturerMaintenance($request->cout, $request->remarques);

        return response()->json([
            'message' => 'Maintenance clôturée avec succès',
            'data' => $maintenance
        ]);
    }
}
