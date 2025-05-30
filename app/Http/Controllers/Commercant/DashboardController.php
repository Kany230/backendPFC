<?php

namespace App\Http\Controllers\Commercant;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use App\Models\Reclamation;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF;
use App\Mail\ReservationAcceptee;
use Illuminate\Support\Facades\Mail;

class DashboardController extends Controller
{
    public function index()
    {
        $commercant = Auth::user();
        
        $reservation_en_cours = Reservation::with(['local.batiment'])
            ->where('commercant_id', $commercant->id)
            ->whereIn('statut', ['en_attente', 'acceptee'])
            ->latest()
            ->first();

        $paiements_valides = Paiement::where('commercant_id', $commercant->id)
            ->where('statut', 'valide')
            ->orderBy('created_at', 'desc')
            ->get();

        $paiements_en_attente = Paiement::where('commercant_id', $commercant->id)
            ->where('statut', 'en_attente')
            ->orderBy('created_at', 'desc')
            ->get();

        $reclamations = Reclamation::where('commercant_id', $commercant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('commercant.dashboard', compact(
            'commercant',
            'reservation_en_cours',
            'paiements_valides',
            'paiements_en_attente',
            'reclamations'
        ));
    }

    public function downloadContrat($reservation_id)
    {
        $reservation = Reservation::with(['commercant', 'local.batiment'])
            ->findOrFail($reservation_id);

        // Vérifier que le commerçant est bien le propriétaire de la réservation
        if ($reservation->commercant_id !== Auth::id()) {
            abort(403);
        }

        // Générer le PDF du contrat
        $pdf = PDF::loadView('commercant.contrat', compact('reservation'));

        return $pdf->download('contrat_location_commercial_' . $reservation_id . '.pdf');
    }

    public function storePaiement(Request $request)
    {
        $validated = $request->validate([
            'montant' => 'required|numeric|min:0',
            'mode_paiement' => 'required|in:especes,virement,cheque',
            'justificatif' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        $justificatif_path = $request->file('justificatif')->store('justificatifs', 'public');

        $paiement = Paiement::create([
            'commercant_id' => Auth::id(),
            'montant' => $validated['montant'],
            'mode_paiement' => $validated['mode_paiement'],
            'justificatif' => $justificatif_path,
            'statut' => 'en_attente'
        ]);

        return redirect()
            ->route('commercant.dashboard')
            ->with('success', 'Votre paiement a été soumis et est en attente de validation.');
    }

    public function storeReclamation(Request $request)
    {
        $validated = $request->validate([
            'sujet' => 'required|string|max:255',
            'description' => 'required|string',
            'priorite' => 'required|in:basse,moyenne,haute,urgente'
        ]);

        $reclamation = Reclamation::create([
            'commercant_id' => Auth::id(),
            'local_id' => Auth::user()->local_id,
            'sujet' => $validated['sujet'],
            'description' => $validated['description'],
            'priorite' => $validated['priorite'],
            'statut' => 'nouveau'
        ]);

        return redirect()
            ->route('commercant.dashboard')
            ->with('success', 'Votre réclamation a été enregistrée.');
    }

    public function showReclamation(Reclamation $reclamation)
    {
        // Vérifier que le commerçant est bien le propriétaire de la réclamation
        if ($reclamation->commercant_id !== Auth::id()) {
            abort(403);
        }

        return view('commercant.reclamations.show', compact('reclamation'));
    }

    public function showPaiement(Paiement $paiement)
    {
        // Vérifier que le commerçant est bien le propriétaire du paiement
        if ($paiement->commercant_id !== Auth::id()) {
            abort(403);
        }

        return view('commercant.paiements.show', compact('paiement'));
    }

    public function notifyReservationAcceptee(Reservation $reservation)
    {
        Mail::to($reservation->commercant->email)
            ->send(new ReservationAcceptee($reservation));

        return response()->json([
            'message' => 'Email de notification envoyé avec succès'
        ]);
    }
} 