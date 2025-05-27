<?php

namespace App\Http\Controllers;

use App\Models\Alerte;
use Illuminate\Support\Facades\Auth;
use Illuminate\Console\View\Components\Alert;
use Illuminate\Http\Request;

class AlerteController extends Controller
{
    public function index(Request $request)
    {
        $utilisateur = Auth::user();
        $nonLues = $request->input('non_vues', false);

        $query = Alerte::where('user_id', $utilisateur->id);

        if ($nonLues) {
            $query->where('vue', false);
        }

        $alertes = $query->orderBy('priorite', 'desc')
                         ->orderBy('dateEcheance', 'asc')
                         ->paginate($request->input('perPage', 15));

        return response()->json([
            'status' => 'success',
            'data' => $alertes
        ]);
    }

    /**
     * Marquer une alerte comme lue
     */
    public function marquerCommeLue($id)
    {

        /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();

        $alerte = Alerte::where('user_id', $utilisateur->id)->find($id);

        if (!$alerte) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alerte non trouvée'
            ], 404);
        }

        $alerte->marquerVue();

        return response()->json([
            'status' => 'success',
            'message' => 'Alerte marquée comme vue'
        ]);
    }

    /**
     * Marquer toutes les alertes comme lues
     */
    public function marquerToutesCommeLues()
    {
        /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();

        Alerte::where('user_id', $utilisateur->id)
              ->where('vue', false)
              ->update(['vue' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Toutes les alertes ont été marquées comme vues'
        ]);
    }

    /**
     * Supprimer une alerte
     */
    public function destroy($id)
    {
        /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();

        $alerte = Alerte::where('user_id', $utilisateur->id)->find($id);

        if (!$alerte) {
            return response()->json([
                'status' => 'error',
                'message' => 'Alerte non trouvée'
            ], 404);
        }

        $alerte->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Alerte supprimée'
        ]);
    }

    /**
     * Obtenir le nombre d’alertes non lues
     */
    public function getNombreNonLues()
    {
        /** @var \App\Models\User$user **/
        $utilisateur = Auth::user();

        $nombre = Alerte::where('user_id', $utilisateur->id)
                        ->where('vue', false)
                        ->count();

        return response()->json([
            'status' => 'success',
            'data' => ['nombre' => $nombre]
        ]);
    }

}
