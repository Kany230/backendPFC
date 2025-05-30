<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\Reclamation;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;

class DashboardController extends Controller
{
    public function index()
    {
        $etudiant = Auth::user();
        
        $reservation_en_cours = Reservation::with(['chambre.pavillon.batiment'])
            ->where('etudiant_id', $etudiant->id)
            ->whereIn('statut', ['en_attente', 'acceptee'])
            ->latest()
            ->first();

        $paiements_valides = Paiement::where('etudiant_id', $etudiant->id)
            ->where('statut', 'valide')
            ->orderBy('created_at', 'desc')
            ->get();

        $paiements_en_attente = Paiement::where('etudiant_id', $etudiant->id)
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->get();

        $reclamations = Reclamation::where('etudiant_id', $etudiant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('etudiant.dashboard', compact(
            'etudiant',
            'reservation_en_cours',
            'paiements_valides',
            'paiements_en_attente',
            'reclamations'
        ));
    }

    public function downloadContrat($reservation_id)
    {
        $reservation = Reservation::with(['etudiant', 'chambre.pavillon.batiment'])
            ->findOrFail($reservation_id);

        // Vérifier que l'étudiant est bien le propriétaire de la réservation
        if ($reservation->etudiant_id !== Auth::id()) {
            abort(403);
        }

        // Générer le PDF du contrat
        $pdf = PDF::loadView('etudiant.contrat', compact('reservation'));

        return $pdf->download('contrat_location_' . $reservation_id . '.pdf');
    }

    public function storePaiement(Request $request)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0',
            'periode' => 'required|string',
            'mode_paiement' => 'required|string|in:especes,virement,mobile_money'
        ]);

        $paiement = Paiement::create([
            'etudiant_id' => Auth::id(),
            'montant' => $request->montant,
            'periode' => $request->periode,
            'mode_paiement' => $request->mode_paiement,
            'statut' => 'en_attente'
        ]);

        // Rediriger vers la facture
        return redirect()
            ->route('factures.download', $paiement->id)
            ->with('success', 'Votre paiement a été enregistré. La facture est en cours de téléchargement.');
    }

    public function storeReclamation(Request $request)
    {
        $validated = $request->validate([
            'sujet' => 'required|string|max:255',
            'description' => 'required|string',
            'priorite' => 'required|in:basse,moyenne,haute,urgente'
        ]);

        $reclamation = Reclamation::create([
            'etudiant_id' => Auth::id(),
            'chambre_id' => Auth::user()->chambre_id,
            'sujet' => $validated['sujet'],
            'description' => $validated['description'],
            'priorite' => $validated['priorite'],
            'statut' => 'nouveau'
        ]);

        return redirect()
            ->route('etudiant.dashboard')
            ->with('success', 'Votre réclamation a été enregistrée.');
    }

    public function showReclamation(Reclamation $reclamation)
    {
        // Vérifier que l'étudiant est bien le propriétaire de la réclamation
        if ($reclamation->etudiant_id !== Auth::id()) {
            abort(403);
        }

        return view('etudiant.reclamations.show', compact('reclamation'));
    }

    public function showPaiement(Paiement $paiement)
    {
        if ($paiement->etudiant_id !== Auth::id()) {
            abort(403);
        }

        return view('etudiant.paiements.show', compact('paiement'));
    }
} 