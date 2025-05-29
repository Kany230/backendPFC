<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calque extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'description',
        'visible',
        'ordre'
    ];

    protected $casts = [
        'visible' => 'boolean',
        'ordre' => 'integer'
    ];

    // Règles de validation
    public static $rules = [
        'nom' => 'required|string|max:255',
        'description' => 'nullable|string',
        'visible' => 'required|boolean',
        'ordre' => 'required|integer|min:0'
    ];

    public function elements()
    {
        return $this->hasMany(CartographieElement::class);
    }

    // Récupérer les éléments avec leurs informations complètes
    public function getElementsComplets()
    {
        return $this->elements()
            ->with(['batiment', 'local'])
            ->orderBy('type')
            ->get();
    }

    // Mettre à jour la visibilité
    public function updateVisibility($visible)
    {
        $this->update(['visible' => $visible]);
    }

    // Mettre à jour l'ordre
    public function updateOrdre($ordre)
    {
        $this->update(['ordre' => $ordre]);
    }
} 