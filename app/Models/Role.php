<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
       protected $fillable = [
        'nom', 'description'
    ];
    
    public function utilisateurs()
    {
        return $this->belongsToMany(User::class);
    }
    
    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
    
    // Vérifier si le rôle a une permission spécifique
    public function hasPermission($permissionNom)
    {
        return $this->permissions()->where('nom', $permissionNom)->exists();
    }
    
    // Ajouter une permission au rôle
    public function assignPermission($permissionNom)
    {
        $permission = Permission::where('nom', $permissionNom)->first();
        
        if ($permission && !$this->hasPermission($permissionNom)) {
            $this->permissions()->attach($permission->id);
        }
    }
}
