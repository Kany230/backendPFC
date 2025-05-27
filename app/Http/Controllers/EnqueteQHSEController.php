<?php

namespace App\Http\Controllers;

use App\Models\EnqueteQHSE;
use Illuminate\Http\Request;

class EnqueteQHSEController extends Controller
{
    public function index(){

        $enquete = EnqueteQHSE::with(['demandeAffectation', 'local', 'agentQHSE'])->get();

        return response()->json([
            'status' => 'success',
            'data' => $enquete
        ]);
    }

    public function show($id){

        $enquete = EnqueteQHSE::with(['demandeAffectation', 'local', 'agentQHSE'])->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $enquete
        ]);
    }

    public function store(Request $request){

        $data = $request->validate([
            'id_demande_affectation' => 'required|exists:demande_affectations,id',
            'id_local' => 'required|exists:locals,id',
            'id_agent_qhse' => 'required|exists:users,id',
        ]);

        $enquete = EnqueteQHSE::create([
            'id_demande_affectation' => $data['id_demande_affectation'],
            'id_local' => $data['id_local'],
            'id_agent_qhse' => $data['id_agent_qhse'],
            'statut' => 'En attente',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Enquete creer',
            'enquete' => $enquete
        ], 201);
    }

    public function destroy($id){

        $enquete = EnqueteQHSE::findOrFail($id);

        if(!$enquete){
            return response()->json([
                'status' => 'error',
                'message' => 'Enquete non trouve'
            ], 404);
        }

        $enquete->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Enquete supprime'
        ]);
    }

    public function update(Request $request, $id){

        $enquete = EnqueteQHSE::findOrFail($id);

        if(!$enquete){
            return response()->json([
                'status' => 'error',
                'message' => 'Enquete non trouve'
            ], 404);
        }

        $enquete->update($request->only(['conclusion', 'conforme', 'statut']));

        return response()->json([
            'status' => 'success',
            'data' => $enquete
        ]);
    }

    public function demarrer($id){

        $enquete = EnqueteQHSE::findOrFail($id);

        if(!$enquete){
            return response()->json([
                'status' => 'error',
                'message' => 'Enquete demarrer'
            ], 404);
        }

        $enquete->demarrer();

        return response()->json([
            'status' => 'success',
            'data' => $enquete
        ]);
    }

     public function completer(Request $request, $id){

        $enquete = EnqueteQHSE::findOrFail($id);

        if(!$enquete){
            return response()->json([
                'status' => 'error',
                'message' => 'Enquete non trouve'
            ], 404);
        }

        $data = $request->validate([
            'conclusion' => 'required|string',
            'conforme' => 'required|boolean',
        ]);

        $enquete->completer($data['conclusion'], $data['conforme']);

        return response()->json([
            'status' => 'success',
            'data' => $enquete
        ]);
    }

    public function generePDF($id){

        $enquete = EnqueteQHSE::findOrFail($id);

        if(!$enquete){
            return response()->json([
                'status' => 'error',
                'message' => 'Enquete non trouve'
            ], 404);
        }

        $pdf = $enquete->genererPDF();

        return $pdf->download("rapport_enquete_qhse_{$id}.pdf");
    }

}
