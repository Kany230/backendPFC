<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RoleUtilisateurController extends Controller
{
    /**
     * Assigner un rôle à un utilisateur
     */
    public function assignerRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);
        
        $utilisateur = User::find($userId);
        
        if (!$utilisateur) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }
        
        $utilisateur->assignRole($request->input('role'));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Rôle assigné avec succès',
            'data' => $utilisateur->load('roles')
        ]);
    }
    
    /**
     * Retirer un rôle d'un utilisateur
     */
    public function retirerRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);
        
        $utilisateur = User::find($userId);
        
        if (!$utilisateur) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }
        
        $utilisateur->removeRole($request->input('role'));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Rôle retiré avec succès',
            'data' => $utilisateur->load('roles')
        ]);
    }
    
    /**
     * Synchroniser les rôles d'un utilisateur
     */
    public function syncRoles(Request $request, $userId)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name'
        ]);
        
        $utilisateur = User::find($userId);
        
        if (!$utilisateur) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }
        
        $utilisateur->syncRoles($request->input('roles'));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Rôles synchronisés avec succès',
            'data' => $utilisateur->load('roles')
        ]);
    }
    
    /**
     * Récupérer les utilisateurs d'un rôle
     */
    public function getUtilisateursRole($roleId)
    {
        $role = Role::find($roleId);
        
        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rôle non trouvé'
            ], 404);
        }
        
        $utilisateurs = $role->users()->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'role' => $role,
                'utilisateurs' => $utilisateurs
            ]
        ]);
    }
    
    /**
     * Assigner une permission directe à un utilisateur
     */
    public function assignerPermission(Request $request, $userId)
    {
        $request->validate([
            'permission' => 'required|exists:permissions,name'
        ]);
        
        $utilisateur = User::find($userId);
        
        if (!$utilisateur) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }
        
        $utilisateur->givePermissionTo($request->input('permission'));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Permission assignée avec succès',
            'data' => $utilisateur->load(['roles', 'permissions'])
        ]);
    }
    
    /**
     * Retirer une permission directe d'un utilisateur
     */
    public function retirerPermission(Request $request, $userId)
    {
        $request->validate([
            'permission' => 'required|exists:permissions,name'
        ]);
        
        $utilisateur = User::find($userId);
        
        if (!$utilisateur) {
            return response()->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé'
            ], 404);
        }
        
        $utilisateur->revokePermissionTo($request->input('permission'));
        
        return response()->json([
            'status' => 'success',
            'message' => 'Permission retirée avec succès',
            'data' => $utilisateur->load(['roles', 'permissions'])
        ]);
    }
}
