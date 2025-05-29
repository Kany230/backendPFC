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
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'sexe',
        'telephone',
        'adresse',
        'role',
        'status',
        'photo',
        // Champs spécifiques aux étudiants
        'niveau_etude',
        'filiere',
        'numero_carte_etudiant',
        // Champs spécifiques aux commerçants
        'numero_cni',
        'cv_url',
        'type_commerce',
        // Champs communs pour la réservation
        'demande_status',
        'type_reservation',
        'date_demande'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_demande' => 'datetime'
    ];

    /**
     * Get the affectations for the user.
     */
    public function affectations()
    {
        return $this->hasMany(Affectation::class, 'id_utilisateur');
    }

    /**
     * Get the reclamations for the user.
     */
    public function reclamations()
    {
        return $this->hasMany(Reclamation::class, 'id_utilisateur');
    }

    /**
     * Get the alertes for the user.
     */
    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }

    /**
     * Get the demandes d'affectation for the user.
     */
    public function demandesAffectation()
    {
        return $this->hasMany(DemandeAffectation::class, 'id_utilisateur');
    }

    /**
     * Get the reservations for the user.
     */
    public function reservations()
    {
        return $this->hasMany(Reservation::class, 'id_utilisateur');
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

