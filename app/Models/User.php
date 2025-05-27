<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, Notifiable, SoftDeletes, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
   protected $fillable = [
        'nom', 
        'prenom', 
        'email', 
        'sexe',
        'telephone', 
        'adresse',
        'password', 
        'statut', 
        'role',
        'photo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
   protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    
    
    public function affectations()
    {
        return $this->hasMany(Affectation::class);
    }
    
    public function demandesAffectation()
    {
        return $this->hasMany(DemandeAffectation::class);
    }
    
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
    
    public function reclamations()
    {
        return $this->hasMany(Reclamation::class);
    }
    
    public function enquetesQHSE()
    {
        return $this->hasMany(EnqueteQHSE::class, 'agent_qhse_id');
    }
    
      public function alerte()
    {
        return $this->hasMany(Alerte::class);
    }
    // Vérifier si l'utilisateur a un rôle spécifique

    public function hasRole($roles)
{
    return in_array($this->role, (array) $roles);
}
    // Ajouter un rôle à l'utilisateur
    public function assignRole($roleName)
    {
        $role = Role::where('nom', $roleName)->first();
        
        if ($role && !$this->hasRole($roleName)) {
            $this->roles()->attach($role->id);
        }
    }
    
    // Générer un contrat pour une affectation
    public function genererContrat($affectationId, $montant, $frequencePaiement, $type)
    {
        $affectation = Affectation::findOrFail($affectationId);
        
        // Vérifier que l'utilisateur est bien associé à cette affectation
        if ($affectation->id_utilisateur !== $this->id) {
            throw new \Exception('Cet utilisateur n\'est pas associé à cette affectation');
        }
        
        // Générer une référence unique pour le contrat
        $reference = 'CONT-' . date('Ymd') . '-' . str_pad($affectationId, 4, '0', STR_PAD_LEFT);
        
        $contrat = Contrat::create([
            'id_affectation' => $affectationId,
            'reference' => $reference,
            'date_debut' => $affectation->date_debut,
            'date_fin' => $affectation->date_fin,
            'montant' => $montant,
            'frequence_paiement' => $frequencePaiement,
            'type' => $type,
            'statut' => 'Actif'
        ]);
        
        // Créer des alertes pour l'échéance du contrat
        $contrat->creerAlertesEcheance();
        
        return $contrat;
    }
    
    // Récupérer les paiements à effectuer
    public function getPaiementsEnAttente()
    {
        return Paiement::whereHas('contrat', function ($query) {
            $query->whereHas('affectation', function ($q) {
                $q->where('id_utilisateur', $this->id);
            });
        })->where('statut', 'En attente')->get();
    }
    
    // Récupérer le solde total dû
    public function getSoldeTotal()
    {
        return $this->getPaiementsEnAttente()->sum('montant');
    }
}

