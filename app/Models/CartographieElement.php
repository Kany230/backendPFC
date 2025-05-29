<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartographieElement extends Model
{
    use HasFactory;

     protected $fillable = [
        'batiment_id',
        'local_id',
        'calque_id',
        'type', 
        'label',
        'coordonnees_x', 
        'coordonnees_y', 
        'largeur', 
        'hauteur', 
        'rotation',
        'couleur', 
        'occupation',
        'details'
    ];
    
    protected $casts = [
        'coordonnees_x' => 'float',
        'coordonnees_y' => 'float',
        'largeur' => 'float',
        'hauteur' => 'float',
        'rotation' => 'float',
        'occupation' => 'float',
        'details' => 'array'
    ];

    // Définir les types autorisés
    const TYPES = ['batiment', 'local', 'equipement'];

    // Règles de validation
    public static $rules = [
        'type' => 'required|in:batiment,local,equipement',
        'label' => 'required|string|max:255',
        'coordonnees_x' => 'required|numeric',
        'coordonnees_y' => 'required|numeric',
        'largeur' => 'required|numeric|min:0',
        'hauteur' => 'required|numeric|min:0',
        'rotation' => 'required|numeric',
        'couleur' => 'nullable|string|max:7',
        'occupation' => 'nullable|numeric|min:0|max:100',
        'batiment_id' => 'nullable|exists:batiments,id',
        'local_id' => 'nullable|exists:locaux,id',
        'calque_id' => 'required|exists:calques,id'
    ];
    
    public function batiment()
    {
        return $this->belongsTo(Batiment::class);
    }
    
    public function local()
    {
        return $this->belongsTo(Local::class);
    }

    public function calque()
    {
        return $this->belongsTo(Calque::class);
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
