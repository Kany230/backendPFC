<?php

namespace App\Http\Controllers;

use App\Models\Batiment;
use Illuminate\Http\Request;

class BatimentController extends Controller
{


    public function index(Request $request){
        //Requete avec filtre, tri et pagination
        $query = Batiment::query();

        //Rechercher par nom
        if($search = $request->input('search')){
            $query->where('nom', 'like', "%$search%");
        }

        //Trier par champs et direction
        $orderBy = $request->input('orderBy', 'nom');
        $orderDir = $request->input('orderDir', 'asc');
        $query->orderBy($orderBy, $orderDir);

        //Controle le nombre d'elements pas page
        $perPage = $request->input('perPage', 15);
        $batiments = $query->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $batiments
        ]);

    }

    public function show($id){

        $batiment = Batiment::with(['locaux.chambres'])->find($id);

        if(!$batiment){
            return response()->json([
                'status' => 'error',
                'message' => 'Batiment non trouve'
            ]);
        }

        return response()->json([
            'status'=> 'success',
            'data'=> $batiment
        ]);

    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string',
            'adresse' => 'required|string',
            'superficie' => 'nullable|numeric',
            'description' => 'nullable|string',
            'dateConstruction' => 'nullable|date',
            'localisation_lat' => 'nullable|numeric',
            'localisation_lng' => 'nullable|numeric',
        ]);

        
        $batiment = Batiment::create($data);

        return response()->json([
            'status' => 'success',
            'data' => $batiment
        ]);
    }

    public function update(Request $request, $id)
    {
        $batiment = Batiment::findOrFail($id);

        $data = $request->validate([
            'nom' => 'required|string',
            'adresse' => 'required|string',
            'superficie' => 'nullable|numeric',
            'description' => 'nullable|string',
            'dateConstruction' => 'nullable|date',
            'localisation_lat' => 'nullable|numeric',
            'localisation_lng' => 'nullable|numeric',
        ]);

        $batiment->update($data);

        return $batiment;
    }

    public function destroy($id){
        
        $batiment = Batiment::find($id);

        if(!$batiment){
            return response()->json([
                'status' => 'error',
                'message' => 'Batiment non trouve'
            ], 404);
        }

        $batiment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Batiment supprime aves success'
        ]);
    }

    public function getStatistiques($id){

        $batiment = Batiment::find($id);

        if(!$batiment){
            return response()->json([
                'status'=>'error',
                'message'=>'Batiment non trouve'
            ], 404);
        }

        $statistiques = $batiment->getStatiqueOccupation();

        return response()->json([
            'status' => 'success',
            'data' => $statistiques
        ]);
    }

    public function getRevenus(Request $request, $id){

        $batiment = Batiment::find($id);

        if(!$batiment){
            return response()->json([
                'status' => 'error',
                'message' => 'Batiment non trouve'
            ], 404);
        }

        $dateDebut = $request->input('dateDebut');
        $dateFin = $request->input('dateFin');

        $revenus = $batiment->getRevenus($dateDebut, $dateFin);

        return response()->json([
            'status' => 'success',
            'data' => [
                'revenus'=> $revenus,
                'periode' => [
                    'debut' => $dateDebut,
                    'fin' => $dateFin
                ]
            ]
        ]);
    }

    public function revenusParMois($id, $annee = null)
    {
        $batiment = Batiment::findOrFail($id);
        return $batiment->getRevenusParMois($annee);
    }

    public function revenusParAnnee($id)
    {
        $batiment = Batiment::findOrFail($id);
        return $batiment->getRevenusParAnnee();
    }

    // Liste des occupants
    public function occupants($id)
    {
        $batiment = Batiment::findOrFail($id);
        return $batiment->getOccupants();
    }
}
