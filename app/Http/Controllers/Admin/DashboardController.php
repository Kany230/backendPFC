<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Batiment;
use App\Models\Local;
use App\Models\User;
use App\Models\Reclamation;
use App\Mail\UserActivated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques générales
        $stats = [
            'batiments_count' => Batiment::count(),
            'locaux_count' => Local::count(),
            'users_count' => User::count(),
            'reclamations_count' => Reclamation::count()
        ];

        // Utilisateurs inactifs
        $inactive_users = User::where('status', 'inactive')
                            ->orderBy('created_at', 'desc')
                            ->get();

        // Utilisateurs récents
        $recent_users = User::where('status', 'active')
                           ->orderBy('created_at', 'desc')
                           ->take(5)
                           ->get();

        // Réclamations récentes
        $recent_reclamations = Reclamation::with('user')
                                         ->orderBy('created_at', 'desc')
                                         ->take(5)
                                         ->get();

        return view('admin.dashboard', compact(
            'stats', 
            'inactive_users', 
            'recent_users', 
            'recent_reclamations'
        ));
    }

    public function activateUser(User $user)
    {
        $user->update(['status' => 'active']);

        // Envoyer l'email de confirmation
        Mail::to($user->email)->send(new UserActivated($user));

        return redirect()
            ->route('admin.dashboard')
            ->with('success', "L'utilisateur a été activé et notifié par email.");
    }

    public function storeGestionnaire(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'gestionnaire',
            'status' => 'active',
        ]);

        // Envoyer les identifiants par email
        Mail::to($user->email)->send(new GestionnaireCreated($user, $validated['password']));

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Le gestionnaire a été créé et ses identifiants ont été envoyés par email.');
    }
} 