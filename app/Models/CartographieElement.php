<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartographieElement extends Model
{
    use HasFactory;
     protected $fillable = [
        'id_batiment', 
        'id_local', 
        'type', 
        'coordonnees_x', 
        'coordonnees_y', 
        'largeur', 
        'hauteur', 
        'rotation',
        'couleur', 
        'label', 
        'details'
    ];
    
    protected $casts = [
        'coordonnees_x' => 'float',
        'coordonnees_y' => 'float',
        'largeur' => 'float',
        'hauteur' => 'float',
        'rotation' => 'float',
        'details' => 'array'
    ];
    
    public function batiment()
    {
        return $this->belongsTo(Batiment::class);
    }
    
    public function local()
    {
        return $this->belongsTo(Local::class);
    }
    
    // Mettre à jour les coordonnées
    public function updatePosition($x, $y)
    {
        $this->update([
            'coordonnees_x' => $x,
            'coordonnees_y' => $y
        ]);
    }
    
    // Mettre à jour les dimensions
    public function updateDimensions($largeur, $hauteur)
    {
        $this->update([
            'largeur' => $largeur,
            'hauteur' => $hauteur
        ]);
    }
    
    // Mettre à jour la rotation
    public function updateRotation($rotation)
    {
        $this->update([
            'rotation' => $rotation
        ]);
    }
}
