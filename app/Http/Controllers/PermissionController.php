<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
        $this->middleware('permission:permission-lister')->only('index');
        $this->middleware('permission:permission-voir')->only('show');
        $this->middleware('permission:permission-creer')->only('store');
        $this->middleware('permission:permission-modifier')->only('update');
        $this->middleware('permission:permission-supprimer')->only('destroy');
    }
    
    /**
     * Récupérer la liste des permissions
     */
    public function index(Request $request)
    {
        $query = Permission::query();
        
        if ($request->filled('search')) {
            $query->where('nom', 'like', '%' . $request->input('search') . '%');
        }
        
        if ($request->filled('group')) {
            $query->where('nom', 'like', $request->input('group') . '-%');
        }
        
        $permissions = $query->orderBy('nom')->get();
        
        // Grouper par module
        $grouped = $permissions->groupBy(function($permission) {
            return explode('-', $permission->nom)[0];
        });
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'permissions' => $permissions,
                'grouped' => $grouped
            ]
        ]);
    }
    
    /**
     * Récupérer une permission spécifique
     */
    public function show($id)
    {
        $permission = Permission::with('roles')->find($id);
        
        if (!$permission) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission non trouvée'
            ], 404);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $permission
        ]);
    }
    
    /**
     * Créer une nouvelle permission
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|unique:permissions,nom',
            'description' => 'nullable|string|max:500'
        ]);
        
        $permission = Permission::create([
            'nom' => $request->input('nom'),
            'description' => $request->input('description'),
            'guard_name' => 'web'
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Permission créée avec succès',
            'data' => $permission
        ], 201);
    }
    
    /**
     * Mettre à jour une permission
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::find($id);
        
        if (!$permission) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission non trouvée'
            ], 404);
        }
        
        $request->validate([
            'nom' => 'required|string|unique:permissions,nom,' . $id,
            'description' => 'nullable|string|max:500'
        ]);
        
        $permission->update([
            'nom' => $request->input('nom'),
            'description' => $request->input('description')
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Permission mise à jour avec succès',
            'data' => $permission
        ]);
    }
    
    /**
     * Supprimer une permission
     */
    public function destroy($id)
    {
        $permission = Permission::find($id);
        
        if (!$permission) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission non trouvée'
            ], 404);
        }
        
        // Vérifier si la permission est assignée à des rôles
        if ($permission->roles()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cette permission est assignée à des rôles et ne peut pas être supprimée'
            ], 400);
        }
        
        $permission->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Permission supprimée avec succès'
        ]);
    }
    
    /**
     * Créer les permissions standard pour un module
     */
    public function creerPermissionsStandard(Request $request)
    {
        $request->validate([
            'module' => 'required|string|alpha_dash',
            'actions' => 'array',
            'actions.*' => 'string|in:lister,voir,creer,modifier,supprimer'
        ]);
        
        $module = $request->input('module');
        $actions = $request->input('actions', ['lister', 'voir', 'creer', 'modifier', 'supprimer']);
        
        $permissions = [];
        
        foreach ($actions as $action) {
            $nomPermission = $module . '-' . $action;
            
            if (!Permission::where('nom', $nomPermission)->exists()) {
                $permission = Permission::create([
                    'nom' => $nomPermission,
                    'description' => ucfirst($action) . ' ' . $module,
                    'guard_name' => 'web'
                ]);
                
                $permissions[] = $permission;
            }
        }
        
        return response()->json([
            'status' => 'success',
            'message' => count($permissions) . ' permissions créées pour le module ' . $module,
            'data' => $permissions
        ], 201);
    }
}
