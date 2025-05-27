<?php

namespace App\Http\Controllers;

use App\Models\Chambre;
use Illuminate\Http\Request;

class ChambreController extends Controller
{
    public function index()
{
    $chambres = Chambre::with('local', 'utilisateurs')->get();

    return response()->json([
        'status' => 'success',
        'data' => $chambres
    ]);
}


    public function show($id)
    {
        $chambre = Chambre::with('local')->find($id);

        if (!$chambre) {
            return response()->json(['error' => 'Chambre non trouvée'], 404);
        }

        return response()->json(['data' => $chambre]);
    }

    public function verifierDisponibilite(Request $request, $id)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut'
        ]);

        $chambre = Chambre::find($id);

        if (!$chambre) {
            return response()->json(['error' => 'Chambre non trouvée'], 404);
        }

        $disponible = $chambre->verifierDisponibilite(
            $request->input('date_debut'),
            $request->input('date_fin')
        );

        return response()->json(['disponible' => $disponible]);
    }
}
