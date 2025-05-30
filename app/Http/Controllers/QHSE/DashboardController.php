<?php

namespace App\Http\Controllers\QHSE;

use App\Http\Controllers\Controller;
use App\Models\DemandeAffectation;
use App\Models\RapportQHSE;
use App\Mail\RapportQHSEComplete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $demandes_inspection = DemandeAffectation::with(['commercant', 'local.batiment', 'rapport_qhse'])
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->get();

        $rapports_recents = RapportQHSE::with(['demande.commercant', 'demande.local'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('qhse.dashboard', compact('demandes_inspection', 'rapports_recents'));
    }

    public function storeRapport(Request $request)
    {
        $validated = $request->validate([
            'demande_id' => 'required|exists:demande_affectations,id',
            'hygiene_proprete' => 'required|in:conforme,non_conforme',
            'hygiene_dechets' => 'required|in:conforme,non_conforme',
            'securite_equipements' => 'required|in:conforme,non_conforme',
            'securite_issues' => 'required|in:conforme,non_conforme',
            'environnement_impact' => 'required|in:conforme,non_conforme',
            'environnement_energie' => 'required|in:conforme,non_conforme',
            'conclusion' => 'required|in:favorable,defavorable',
            'commentaires' => 'required|string'
        ]);

        $demande = DemandeAffectation::findOrFail($validated['demande_id']);

        // Créer ou mettre à jour le rapport
        $rapport = RapportQHSE::updateOrCreate(
            ['demande_id' => $demande->id],
            [
                'hygiene' => [
                    'proprete' => $validated['hygiene_proprete'],
                    'dechets' => $validated['hygiene_dechets']
                ],
                'securite' => [
                    'equipements' => $validated['securite_equipements'],
                    'issues' => $validated['securite_issues']
                ],
                'environnement' => [
                    'impact' => $validated['environnement_impact'],
                    'energie' => $validated['environnement_energie']
                ],
                'conclusion' => $validated['conclusion'],
                'commentaires' => $validated['commentaires'],
                'agent_id' => auth()->id()
            ]
        );

        // Notifier le gestionnaire
        $gestionnaires = User::where('role', 'gestionnaire')->get();
        foreach ($gestionnaires as $gestionnaire) {
            Mail::to($gestionnaire->email)
                ->send(new RapportQHSEComplete($rapport));
        }

        return redirect()
            ->route('qhse.dashboard')
            ->with('success', 'Le rapport a été enregistré et le gestionnaire a été notifié.');
    }

    public function showRapport(RapportQHSE $rapport)
    {
        return view('qhse.rapports.show', compact('rapport'));
    }
} 