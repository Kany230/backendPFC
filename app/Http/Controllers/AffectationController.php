<?php

namespace App\Http\Controllers;

use App\Models\Affectation;
use App\Models\Chambre;
use App\Models\DemandeAffectation;
use App\Models\Local;
use App\Models\User;
use FontLib\Table\Type\loca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use function Illuminate\Log\log;

class AffectationController extends Controller
{
    protected $rules = [
        'id_utilisateur' => 'required|exists:users,id',
        'id_local' => 'required|exists:locals,id',
        'dateDebut' => 'required|date',
        'dateFin' => 'required|date|after:dateDebut',
        'type' => 'required|in:temporaire,permanent'
    ];

    public function index(Request $request)
    {
       $query = Affectation::with(['utilisateur', 'local']);

        // Filtres
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('utilisateur', function ($q2) use ($search) {
                $q2->where('nom', 'like', "%$search%")
                   ->orWhere('prenom', 'like', "%$search%");
              })
                ->orWhereHas('local', function ($q3) use ($search) {
                $q3->where('nom', 'like', "%$search%");
              });
            });
        }

        // Filtres supplémentaires
        if ($utilisateurId = $request->input('id_utilisateur')) {
            $query->where('id_utilisateur', $utilisateurId);
        }

        if ($localId = $request->input('id_local')) {
            $query->where('id_local', $localId);
        }

        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }

        // Tri
        $orderBy = $request->input('orderBy', 'dateDebut');
        $orderDir = $request->input('orderDir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = $request->input('perPage', 15);
        $affectations = $query->paginate($perPage);

        return $this->jsonResponse($affectations, 'Liste des affectations récupérée avec succès');
    }

    public function store(Request $request)
{
        $validated = $request->validate($this->rules);

        // Vérifier la disponibilité du local
        $local = Local::findOrFail($validated['id_local']);
        $disponible = $local->verifierDisponibilite(
            $validated['dateDebut'],
            $validated['dateFin']
        );

        if (!$disponible) {
            return $this->jsonResponse(
                null,
                'Ce local n\'est pas disponible pour la période demandée',
                'error',
                400
            );
    }

        $affectation = Affectation::create($validated);
        $local->update(['disponible' => false]);

        return $this->jsonResponse(
            $affectation->load(['utilisateur', 'local']),
            'Affectation créée avec succès',
            'success',
            201
        );
    }

    public function show($id)
    {
        $affectation = Affectation::with(['utilisateur', 'local'])->findOrFail($id);
        return $this->jsonResponse($affectation);
}

    public function update(Request $request, $id)
    {
        $affectation = Affectation::findOrFail($id);

        $validated = $request->validate([
            'dateDebut' => 'sometimes|required|date',
            'dateFin' => 'sometimes|required|date|after:dateDebut',
            'statut' => 'sometimes|required|in:active,terminee,resiliee'
        ]);

        $affectation->update($validated);

        return $this->jsonResponse(
            $affectation->fresh(['utilisateur', 'local']),
            'Affectation mise à jour avec succès'
        );
    }

    public function destroy($id)
    {
        $affectation = Affectation::findOrFail($id);
        $local = $affectation->local;

        $affectation->delete();
        $local->update(['disponible' => true]);

        return $this->jsonResponse(null, 'Affectation supprimée avec succès');
    }

    public function resilier(Request $request, $id)
    {
        $request->validate(['raison' => 'required|string']);

        $affectation = Affectation::findOrFail($id);

        $affectation->update([
            'statut' => 'resiliee',
            'dateFin' => now(),
            'remarques' => $request->raison
        ]);

        $affectation->local->update(['disponible' => true]);

        return $this->jsonResponse($affectation, 'Affectation résiliée avec succès');
    }

    public function renouveler(Request $request, $id)
    {
        $request->validate([
            'nouvelle_date_fin' => 'required|date|after:today'
        ]);

        $affectation = Affectation::findOrFail($id);
        
        // Vérifier si le renouvellement est possible
        $disponible = $affectation->local->verifierDisponibilite(
            $affectation->dateFin,
            $request->nouvelle_date_fin
        );

        if (!$disponible) {
            return $this->jsonResponse(
                null,
                'Le renouvellement n\'est pas possible pour cette période',
                'error',
                400
            );
        }

        $affectation->update([
            'dateFin' => $request->nouvelle_date_fin,
            'dernierRenouvellement' => now()
        ]);
        
        return $this->jsonResponse($affectation, 'Affectation renouvelée avec succès');
    }

    public function historiqueLocal($localId)
    {
        $historique = Affectation::where('id_local', $localId)
            ->with(['utilisateur', 'contrat'])
            ->orderBy('dateDebut', 'desc')
            ->paginate(15);
        
        return $this->jsonResponse($historique, 'Historique des affectations du local récupéré');
    }

    public function historiqueUtilisateur($userId)
    {
        $historique = Affectation::where('id_utilisateur', $userId)
            ->with(['local', 'contrat'])
            ->orderBy('dateDebut', 'desc')
            ->paginate(15);

        return $this->jsonResponse($historique, 'Historique des affectations de l\'utilisateur récupéré');
    }

    protected function jsonResponse($data, $message = '', $status = 'success', $code = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}

