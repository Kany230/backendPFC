<?php

namespace App\Http\Controllers;

use App\Models\CritereEvaluation;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Return_;

class CritereEvaluationController extends Controller
{
    public function index($id){

        $criteres = CritereEvaluation::where('id_enquete_qhse', $id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $criteres
        ]);
    }

    public function show($id){

        $critere = CritereEvaluation::findOrFail($id);

        if(!$critere){
            return response()->json([
                'status' => 'error',
                'message' => 'Critere non trouve'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $critere
        ]);
    }

    public function store(Request $request){

        $request->validate([
            'id_enquete_qhse' => 'required|exists:enquete_qhses,id',
            'categorie' => 'required|string|in:Securite,Hygiene,Qualite,Environnement',
            'description' => 'required|string',
            'conforme' => 'required|boolean',
            'observation' => 'nullable|string',
            'priorite' => 'required|in:Faible,Moyenne,Elevee,Critique'
        ]);

        $critere = CritereEvaluation::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Critere ajoute',
            'data' => $critere
        ], 201);
    }

    public function update(Request $request, $id){
        $critere = CritereEvaluation::findOrFail($id);

        if(!$critere){
            return response()->json([
                'status' => 'error',
                'message' => 'Critere non trouve'
            ], 404);
        }

        $critere->update($request->all());

        return response()->json([
            'status' => 'error',
            'message' => 'Critere mis a jour',
            'data' => $critere
        ]);
    }

    public function destroy($id){

        $critere = CritereEvaluation::findOrFail($id);

        if(!$critere){
            return response()->json([
                'status' => 'error',
                'message' => 'Critere non trouve'
            ], 404);
        }

        $critere->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Critere supprime'
        ]);
    }

}
