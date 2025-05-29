<?php

namespace App\Http\Controllers;

use App\Models\Chambre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChambreController extends Controller
{
    /**
     * Liste toutes les chambres avec leurs locaux et utilisateurs.
     */
    public function index()
    {
        $chambres = Chambre::with('local', 'utilisateurs')->get();

        return response()->json([
            'status' => 'success',
            'data' => $chambres
        ]);
    }

    /**
     * Liste toutes les chambres qui sont dans des locaux de type "pavillon".
     */
    public function chambresPavillons()
    {
        try {
            // CORRIGÉ: Utilisez la bonne casse pour 'Pavillon'
            $chambres = Chambre::whereHas('local', function ($query) {
                $query->where('type', 'Pavillon'); // Changé de 'pavillon' à 'Pavillon'
            })
            ->with(['local', 'utilisateurs'])
            ->get();

            return response()->json([
                'status' => 'success',
                'message' => 'Chambres des pavillons récupérées',
                'total' => $chambres->count(),
                'data' => $chambres
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur chambresPavillons: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage(),
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Version debug pour les chambres
     */
    public function debugChambres()
    {
        try {
            $totalChambres = Chambre::count();
            $typesLocaux = DB::table('locals')->distinct()->pluck('type');
            $chambresAvecLocal = Chambre::with('local')->limit(5)->get();

            return response()->json([
                'debug_chambres' => [
                    'total_chambres' => $totalChambres,
                    'types_locaux_disponibles' => $typesLocaux,
                    'sample_chambres' => $chambresAvecLocal
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Affiche les détails d'une chambre spécifique.
     */
    public function show($id)
    {
        $chambre = Chambre::with('local', 'utilisateurs')->find($id);

        if (!$chambre) {
            return response()->json(['error' => 'Chambre non trouvée'], 404);
        }

        return response()->json(['data' => $chambre]);
    }

    /**
     * Vérifie si une chambre est disponible sur une période donnée.
     */
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

        if (!method_exists($chambre, 'verifierDisponibilite')) {
            return response()->json(['error' => 'Méthode de disponibilité non définie sur le modèle Chambre.'], 500);
        }

        $disponible = $chambre->verifierDisponibilite(
            $request->input('date_debut'),
            $request->input('date_fin')
        );

        return response()->json(['disponible' => $disponible]);
    }
}
