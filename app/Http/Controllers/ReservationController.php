<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    protected $rules = [
            'id_local' => 'required|exists:locals,id',
            'id_utilisateur' => 'required|exists:users,id',
            'dateDebut' => 'required|date|before_or_equal:dateFin',
            'dateFin' => 'required|date|after_or_equal:dateDebut',
            'motif' => 'nullable|string',
        'remarques' => 'nullable|string'
    ];

    public function index(Request $request)
    {
        $query = Reservation::with(['local', 'utilisateur']);

        // Filtres
        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('motif', 'like', "%$search%")
                  ->orWhereHas('local', function($q2) use ($search) {
                      $q2->where('nom', 'like', "%$search%");
                  })
                  ->orWhereHas('utilisateur', function($q3) use ($search) {
                      $q3->where('nom', 'like', "%$search%")
                        ->orWhere('prenom', 'like', "%$search%");
                  });
            });
        }

        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }

        if ($localId = $request->input('id_local')) {
            $query->where('id_local', $localId);
        }

        if ($utilisateurId = $request->input('id_utilisateur')) {
            $query->where('id_utilisateur', $utilisateurId);
        }

        // Tri
        $orderBy = $request->input('orderBy', 'dateDebut');
        $orderDir = $request->input('orderDir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        // Pagination
        $perPage = $request->input('perPage', 15);
        $reservations = $query->paginate($perPage);

        return $this->jsonResponse($reservations, 'Liste des réservations récupérée avec succès');
    }

    public function show($id)
    {
        $reservation = Reservation::with(['local', 'utilisateur'])->findOrFail($id);
        return $this->jsonResponse($reservation);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules);

        $reservation = new Reservation($validated);
        $reservation->statut = 'En attente';

        if ($reservation->verifierChevauchement()) {
            return $this->jsonResponse(
                null,
                'La réservation chevauche une autre réservation approuvée',
                'error',
                409
            );
        }

        $reservation->save();

        return $this->jsonResponse(
            $reservation->load(['local', 'utilisateur']),
            'Réservation créée avec succès',
            'success',
            201
        );
    }

    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);
                
        $validated = $request->validate([
            'id_local' => 'sometimes|exists:locals,id',
            'dateDebut' => 'sometimes|date|before_or_equal:dateFin',
            'dateFin' => 'sometimes|date|after_or_equal:dateDebut',
            'motif' => 'nullable|string',
            'remarques' => 'nullable|string'
        ]);

        $reservation->fill($validated);

        if ($reservation->verifierChevauchement()) {
            return $this->jsonResponse(
                null,
                'La modification entraîne un chevauchement avec une autre réservation approuvée',
                'error',
                409
            );
        }

        $reservation->save();

        return $this->jsonResponse(
            $reservation->fresh(['local', 'utilisateur']),
            'Réservation mise à jour avec succès'
        );
    }

    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return $this->jsonResponse(null, 'Réservation supprimée avec succès');
    }

    public function approuver($id)
    {
        $reservation = Reservation::findOrFail($id);
                                               
        if ($reservation->statut !== 'En attente') {
            return $this->jsonResponse(
                null,
                'La réservation ne peut pas être approuvée car elle n\'est pas en attente',
                'error',
                400
            );
        }

        if ($reservation->verifierChevauchement()) {
            return $this->jsonResponse(
                null,
                'La réservation ne peut pas être approuvée car elle chevauche une autre réservation',
                'error',
                409
            );
        }

        $reservation->update(['statut' => 'Approuvée']);

        return $this->jsonResponse(
            $reservation->fresh(['local', 'utilisateur']),
            'Réservation approuvée avec succès'
        );
    }

    public function annuler(Request $request, $id)
    {
        $validated = $request->validate([
            'raison' => 'required|string'
        ]);
        
        $reservation = Reservation::findOrFail($id);
                                               
        if ($reservation->statut === 'Annulée') {
            return $this->jsonResponse(
                null,
                'La réservation est déjà annulée',
                'error',
                400
            );
        }
        
        $reservation->update([
            'statut' => 'Annulée',
            'remarques' => $validated['raison']
        ]);

        return $this->jsonResponse(
            $reservation->fresh(['local', 'utilisateur']),
            'Réservation annulée avec succès'
        );
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
