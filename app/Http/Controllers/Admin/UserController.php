<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index()
    {
        $users = User::whereIn('role', ['qhse', 'chef_pavillon', 'gestionnaire'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'telephone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:qhse,chef_pavillon,gestionnaire',
            'batiment_id' => 'required_if:role,chef_pavillon|exists:batiments,id',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'batiment_id' => $request->batiment_id,
            'statut' => 'actif'
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'L\'utilisateur a été créé avec succès.');
    }

    public function edit(User $user)
    {
        if (!in_array($user->role, ['qhse', 'chef_pavillon', 'gestionnaire'])) {
            abort(403);
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if (!in_array($user->role, ['qhse', 'chef_pavillon', 'gestionnaire'])) {
            abort(403);
        }

        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'telephone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'batiment_id' => 'required_if:role,chef_pavillon|exists:batiments,id',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $userData = [
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'batiment_id' => $request->batiment_id,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'L\'utilisateur a été mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        if (!in_array($user->role, ['qhse', 'chef_pavillon', 'gestionnaire'])) {
            abort(403);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'L\'utilisateur a été supprimé avec succès.');
    }
} 