<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function index(){

        $reservations = Reservation::with(['local', 'utilisateur'])->get();

        return response()->json([
            'status' => 'success',
            'data' => $reservations
        ]);
    }

    public function show($id){

        $reservation = Reservation::with(['local', 'utilisateur'])->findOrFail($id);
                                                                               
        if(!$reservation){
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation non trouve'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $reservation
        ]);
    }

    public function store(Request $request){

        $request->validate([
            'id_local' => 'required|exists:locals,id',
            'id_utilisateur' => 'required|exists:users,id',
            'dateDebut' => 'required|date|before_or_equal:dateFin',
            'dateFin' => 'required|date|after_or_equal:dateDebut',
            'motif' => 'nullable|string',
            'remarques' => 'nullable|string',
        ]);

        $reservation = new Reservation($request->all());
        $reservation->statut = 'En attente';

        if ($reservation->verifierChevauchement()) {
            return response()->json([
                'message' => 'La reservation chevauche une autre reservation approuvée.'
            ], 409);
        }

        $reservation->save();

        return response()->json([
            'message' => 'Reservation cree',
            'data' => $reservation
        ], 201);
    }

    public function update(Request $request, $id){
        
        $reservation = Reservation::findOrFail($id);
                
        if(!$reservation){
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation non trouve'
            ], 404);
        }

        $request->validate([
            'id_local' => 'sometimes|exists:locals,id',
            'dateDebut' => 'sometimes|date|before_or_equal:dateFin',
            'dateFin' => 'sometimes|date|after_or_equal:dateDebut',
            'motif' => 'nullable|string',
            'remarques' => 'nullable|string',
        ]);

        $reservation->fill($request->all());

        if ($reservation->verifierChevauchement()) {
            return response()->json([
                'message' => 'La modification entrine un chevauchement avec une autre reservation approuvee.'
            ], 409);
        }

        $reservation->save();

        return response()->json([
            'message' => 'Reservation mise a jour',
            'data' => $reservation
        ]);
    }

    public function destroy($id){

        $reservation = Reservation::findOrFail($id);
                                               
        if(!$reservation){
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation non trouve'
            ], 404);
        }
        
        $reservation->delete();

        return response()->json([
            'message' => 'Reservation supprime'
        ]);
    }

    public function approuver($id){

        $reservation = Reservation::findOrFail($id);
                                               
        if(!$reservation){
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation non trouve'
            ], 404);
        }

        if ($reservation->verifierChevauchement()) {
            return response()->json([
                'message' => 'Impossible d\'approuver, cette reservation chevauche une reservation existante.'
            ], 409);
        }

        $reservation->approuver();

        return response()->json([
            'message' => 'Reservation approuvee avec succes',
            'data' => $reservation
        ]);
    }

    public function annuler(Request $request, $id){
        
        $reservation = Reservation::findOrFail($id);
                                               
        if(!$reservation){
            return response()->json([
                'status' => 'error',
                'message' => 'Reservation non trouve'
            ], 404);
        }
        
        $raison = $request->input('raison', null);

        $reservation->annuler($raison);

        return response()->json([
            'message' => 'Reservation annulee avec succes',
            'data' => $reservation
        ]);
    }

    
}
