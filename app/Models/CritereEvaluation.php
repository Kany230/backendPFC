<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CritereEvaluation extends Model
{
    use HasFactory;
 protected $fillable = [
        'id_enquete_qhse', 
        'categorie', 
        'description',
        'conforme', 
        'observation', 
        'priorite'
    ];
    
    protected $casts = [
        'conforme' => 'boolean'
    ];
    
    public function enqueteQHSE()
    {
        return $this->belongsTo(EnqueteQHSE::class);
    }
}
