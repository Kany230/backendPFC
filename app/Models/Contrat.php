<?php

namespace App\Models;

use App\Notifications\FactureCreee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\PdfService;

class Contrat extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'id_affectation', 
        'reference', 
        'dateDebut', 
        'dateFin',
        'montant', 
        'frequence_paiement', 
        'type', 
        'statut'
    ];
    
    protected $dates = [
        'dateDebut', 'dateFin'
    ];
    
    public function affectation()
    {
        return $this->belongsTo(Affectation::class);
    }
    
    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }
    
    public function alertes()
    {
        return $this->hasMany(Alerte::class, 'source_id')
            ->where('type', 'Contrat');
    }
    
    // Renouveler un contrat
    public function renouveler($nouvelleDateFin)
    {
        $this->update([
            'dateFin' => $nouvelleDateFin
        ]);
        
        // Reprogrammer les alertes
        $this->creerAlertesEcheance();
    }
    
    // Résilier un contrat
    public function resilier($raison = null)
    {
        $this->update([
            'statut' => 'Résilié'
        ]);
        
        // Fermer les alertes
        $this->alertes()->update(['vue' => true]);
    }
    
    // Générer une facture
    public function genererFacture($montant, $dateEcheance)
    {
        // Créer un nouveau paiement à effectuer
        $paiement = Paiement::create([
            'id_contrat' => $this->id,
            'montant' => $montant,
            'dateEcheance' => $dateEcheance,
            'statut' => 'En attente'
        ]);
        
        // Créer une alerte pour l'échéance
        Alerte::create([
            'type' => 'Paiement',
            'source_id' => $paiement->id,
            'message' => "Facture à payer pour le contrat {$this->reference}",
            'dateCreation' => now(),
            'dateEcheance' => $dateEcheance,
            'priorite' => 'Élevée'
        ]);
        
        // Notifier l'utilisateur
        $this->affectation->utilisateur->notify(new FactureCreee($paiement));
        
        return $paiement;
    }
    
    // Créer des alertes pour l'échéance du contrat
    public function creerAlertesEcheance()
    {
        // Supprimer les anciennes alertes
        $this->alertes()->delete();
        
        // Créer une alerte 1 mois avant l'échéance
        $dateAlerte = (clone $this->date_fin)->subMonth();
        
        if ($dateAlerte > now()) {
            Alerte::create([
                'type' => 'Contrat',
                'source_id' => $this->id,
                'message' => "Le contrat {$this->reference} expire dans 1 mois",
                'dateCreation' => now(),
                'dateEcheance' => $dateAlerte,
                'priorite' => 'Moyenne'
            ]);
        }
        
        // Créer une alerte 1 semaine avant l'échéance
        $dateAlerte = (clone $this->date_fin)->subWeek();
        
        if ($dateAlerte > now()) {
            Alerte::create([
                'type' => 'Contrat',
                'source_id' => $this->id,
                'message' => "Le contrat {$this->reference} expire dans 1 semaine",
                'dateCreation' => now(),
                'dateEcheance' => $dateAlerte,
                'priorite' => 'Élevée'
            ]);
        }
    }
    
    // Générer un document PDF du contrat
    public function genererPDF()
    {
        // Voir l'implémentation dans le service PDF
        return app(PdfService::class)->genererContratPDF($this);
    }
}
