<?php

namespace App\Models;

use App\Notifications\PaiementValide;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\PdfService;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paiement extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'id_utilisateur',
        'id_affectation', 
        'montant', 
        'date_paiement', 
        'date_echeance',
        'methode_paiement', 
        'reference', 
        'statut', 
        'enregistre_par'
    ];
    
    protected $dates = [
        'date_paiement', 'date_echeance'
    ];
    
    public function affectation()
    {
        return $this->belongsTo(Affectation::class);
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class);
    }
    
    public function alertes()
    {
        return $this->hasMany(Alerte::class, 'source_id')
            ->where('type', 'Paiement');
    }
    
    // Valider un paiement
    public function valider($methodePaiement, $reference = null)
    {
        $this->update([
            'date_paiement' => now(),
            'methode_paiement' => $methodePaiement,
            'reference' => $reference,
            'statut' => 'Validé'
        ]);
        
        // Fermer les alertes associées
        $this->alertes()->update(['vue' => true]);
        
        // Notifier l'utilisateur
        $utilisateur = $this->contrat->affectation->utilisateur;
        $utilisateur->notify(new PaiementValide($this));
        
        return $this;
    }
    
    // Annuler un paiement
    public function annuler($raison = null)
    {
        $this->update([
            'statut' => 'Annulé',
            'remarques' => $raison ?? $this->remarques
        ]);
    }
    
    // Générer une quittance de paiement au format PDF
    public function genererQuittance()
    {
        // Vérifier que le paiement est validé
        if ($this->statut !== 'Validé') {
            throw new \Exception('Ce paiement n\'a pas encore été validé');
        }
        
        // Voir l'implémentation dans le service PDF
        return app(PdfService::class)->genererQuittancePDF($this);
    }
}
