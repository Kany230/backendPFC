<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    
    /**
     * Récupérer la liste des rôles
     */
    public function index(Request $request)
    {
        $query = Role::with('permissions');
        
        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->input('search') . '%');
        }
        
        $roles = $query->orderBy('nom')->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $roles
        ]);
    }
    
    /**
     * Récupérer un rôle spécifique
     */
    public function show($id)
    {
        $role = Role::with('permissions')->find($id);
        
        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rôle non trouvé'
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $role
        ]);
    }
    
    /**
     * Créer un nouveau rôle
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|unique:roles,nom',
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,nom'
        ]);
        
        $role = Role::create([
            'nom' => $request->input('nom'),
            'description' => $request->input('description'),
            'guard_name' => 'web'
        ]);
        
        // Assigner les permissions si spécifiées
        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Rôle créé avec succès',
            'data' => $role->load('permissions')
        ], 201);
    }
    
    /**
     * Mettre à jour un rôle
     */
    public function update(Request $request, $id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rôle non trouvé'
            ], 404);
        }
        
        $request->validate([
            'nom' => 'required|string|unique:roles,nom,' . $id,
            'description' => 'nullable|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,nom'
        ]);
        
        $role->update([
            'nom' => $request->input('nom'),
            'description' => $request->input('description')
        ]);
        
        // Synchroniser les permissions
        if ($request->has('permissions')) {
            $role->syncPermissions($request->input('permissions'));
        }
        
        return response()->json([
            'status' => 'success',
            'message' => 'Rôle mis à jour avec succès',
            'data' => $role->load('permissions')
        ]);
    }
    
    /**
     * Supprimer un rôle
     */
    public function destroy($id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rôle non trouvé'
            ], 404);
        }
        
        // Vérifier si le rôle est assigné à des utilisateurs
        if ($role->users()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Ce rôle est assigné à des utilisateurs et ne peut pas être supprimé'
            ], 400);
        }
        
        $role->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Rôle supprimé avec succès'
        ]);
    }
    
    /**
     * Assigner/Retirer des permissions à un rôle
     */
    public function syncPermissions(Request $request, $id)
    {
        $role = Role::find($id);
        
        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rôle non trouvé'
            ], 404);
        }
        
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,nom'
        ]);
        
        $role->syncPermissions($request->input('permissions'));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Permissions synchronisées avec succès',
            'data' => $role->load('permissions')
        ]);
    }
}
