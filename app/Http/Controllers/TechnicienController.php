<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TechnicienController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!in_array(Auth::user()->role, ['gestionnaire', 'chef_pavillon'])) {
                abort(403);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $user = Auth::user();
        $query = User::where('role', 'technicien');

        // Si c'est un chef de pavillon, on ne montre que les techniciens de son bâtiment
        if ($user->role === 'chef_pavillon') {
            $query->where('batiment_id', $user->batiment_id);
        }

        $techniciens = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('techniciens.index', compact('techniciens'));
    }

    public function create()
    {
        return view('techniciens.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telephone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'specialite' => 'required|string|max:100',
            'batiment_id' => 'required|exists:batiments,id'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Vérifier que le chef de pavillon ne crée des techniciens que pour son bâtiment
        $user = Auth::user();
        if ($user->role === 'chef_pavillon' && $user->batiment_id != $request->batiment_id) {
            return back()
                ->withErrors(['batiment_id' => 'Vous ne pouvez créer des techniciens que pour votre bâtiment.'])
                ->withInput();
        }

        $technicien = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => Hash::make($request->password),
            'role' => 'technicien',
            'specialite' => $request->specialite,
            'batiment_id' => $request->batiment_id,
            'statut' => 'actif'
        ]);

        return redirect()
            ->route('techniciens.index')
            ->with('success', 'Le technicien a été créé avec succès.');
    }

    public function edit(User $technicien)
    {
        if ($technicien->role !== 'technicien') {
            abort(404);
        }

        // Vérifier que le chef de pavillon ne modifie que les techniciens de son bâtiment
        $user = Auth::user();
        if ($user->role === 'chef_pavillon' && $user->batiment_id != $technicien->batiment_id) {
            abort(403);
        }

        return view('techniciens.edit', compact('technicien'));
    }

    public function update(Request $request, User $technicien)
    {
        if ($technicien->role !== 'technicien') {
            abort(404);
        }

        // Vérifier que le chef de pavillon ne modifie que les techniciens de son bâtiment
        $user = Auth::user();
        if ($user->role === 'chef_pavillon' && $user->batiment_id != $technicien->batiment_id) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $technicien->id,
            'telephone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'specialite' => 'required|string|max:100',
            'batiment_id' => 'required|exists:batiments,id'
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Vérifier que le chef de pavillon ne change pas le bâtiment
        if ($user->role === 'chef_pavillon' && $user->batiment_id != $request->batiment_id) {
            return back()
                ->withErrors(['batiment_id' => 'Vous ne pouvez pas changer le bâtiment du technicien.'])
                ->withInput();
        }

        $userData = [
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'specialite' => $request->specialite,
            'batiment_id' => $request->batiment_id,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $technicien->update($userData);

        return redirect()
            ->route('techniciens.index')
            ->with('success', 'Le technicien a été mis à jour avec succès.');
    }

    public function destroy(User $technicien)
    {
        if ($technicien->role !== 'technicien') {
            abort(404);
        }

        // Vérifier que le chef de pavillon ne supprime que les techniciens de son bâtiment
        $user = Auth::user();
        if ($user->role === 'chef_pavillon' && $user->batiment_id != $technicien->batiment_id) {
            abort(403);
        }

        $technicien->delete();

        return redirect()
            ->route('techniciens.index')
            ->with('success', 'Le technicien a été supprimé avec succès.');
    }
} 