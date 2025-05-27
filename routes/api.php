<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoleUtilisateurController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\BatimentController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\CartographieElementController;
use App\Http\Controllers\ChambreController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\CritereEvaluationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandeAffectationController;
use App\Http\Controllers\EnqueteQHSEController;
use App\Http\Controllers\EquipementController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\ReclamationController;
use App\Http\Controllers\ReservationController;

// Routes publiques
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/reinitialiserPassword', [AuthController::class, 'reinitialiserPassword']);

// Routes protégées par Sanctum


    Route::get('/admin/en-attente', [AdminController::class, 'listeEnAttente']);
    Route::post('/admin/valider/{id}', [AdminController::class, 'validerInscription']);

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/changerPassword', [AuthController::class, 'changerPassword']);

    // Rôles & Permissions
    Route::apiResource('roles', RoleController::class);
    Route::post('roles/{id}/permissions', [RoleController::class, 'syncPermissions']);

    Route::apiResource('permissions', PermissionController::class);
    Route::post('permissions/modules', [PermissionController::class, 'creerPermissionsStandard']);

    // Assignation rôles/utilisateurs
    Route::post('utilisateurs/{id}/roles', [RoleUtilisateurController::class, 'assignerRole']);
    Route::delete('utilisateurs/{id}/roles', [RoleUtilisateurController::class, 'retirerRole']);
    Route::put('utilisateurs/{id}/roles', [RoleUtilisateurController::class, 'syncRoles']);
    Route::post('utilisateurs/{id}/permissions', [RoleUtilisateurController::class, 'assignerPermission']);
    Route::delete('utilisateurs/{id}/permissions', [RoleUtilisateurController::class, 'retirerPermission']);
    Route::get('roles/{id}/utilisateurs', [RoleUtilisateurController::class, 'getUtilisateursRole']);

    // Utilisateurs
    Route::get('utilisateurs', [UsersController::class, 'index']);
    Route::post('utilisateurs', [UsersController::class, 'store']);
    Route::get('utilisateurs/{id}', [UsersController::class, 'show']);
    Route::put('utilisateurs/{id}', [UsersController::class, 'update']);
    Route::delete('utilisateurs/{id}', [UsersController::class, 'destroy']);
    Route::get('profil', [UsersController::class, 'getProfil']);
    Route::put('profil', [UsersController::class, 'updateProfil']);
    Route::patch('utilisateurs/{id}/changer-statut', [UsersController::class, 'changerStatut']);

    // Affectations
    Route::prefix('affectations')->group(function () {
        Route::get('/', [AffectationController::class, 'index']);
        Route::get('{id}', [AffectationController::class, 'show']);
        Route::post('/', [AffectationController::class, 'store']);
        Route::put('{id}', [AffectationController::class, 'update']);
        Route::delete('{id}', [AffectationController::class, 'destroy']);
        Route::post('{id}/resilier', [AffectationController::class, 'resilier']);
        Route::post('{id}/renouveler', [AffectationController::class, 'renouveler']);
        Route::post('demandes/{id}/approuver', [AffectationController::class, 'approuveDemande']);
        Route::get('utilisateurs/{id}/affectations', [AffectationController::class, 'getAffectationsUtilisateur']);
        Route::post('demandes/{id}/valider-et-enquete', [AffectationController::class, 'validerDemandeEtLancerEnquete']);
        Route::get('/affectations', [AffectationController::class, 'getEtudiantsNonAffectes']);

    });

    // Alertes
    Route::prefix('alertes')->group(function () {
        Route::get('/', [AlerteController::class, 'index']);
        Route::patch('{id}/lue', [AlerteController::class, 'marquerCommeLue']);
        Route::patch('toutes-lues', [AlerteController::class, 'marquerToutesCommeLues']);
        Route::delete('{id}', [AlerteController::class, 'destroy']);
        Route::get('non-lues/nombre', [AlerteController::class, 'getNombreNonLues']);
    });

    // Bâtiments
    Route::prefix('batiments')->group(function () {
        Route::get('/', [BatimentController::class, 'index']);
        Route::get('{id}', [BatimentController::class, 'show']);
        Route::post('/batiments', [BatimentController::class, 'store']);
        Route::put('{id}', [BatimentController::class, 'update']);
        Route::patch('{id}', [BatimentController::class, 'update']);
        Route::delete('{id}', [BatimentController::class, 'destroy']);
        Route::get('{id}/statistiques', [BatimentController::class, 'getStatistiques']);
        Route::get('{id}/revenus', [BatimentController::class, 'getRevenus']);
        Route::get('{id}/revenus-par-mois/{annee?}', [BatimentController::class, 'revenusParMois']);
        Route::get('{id}/revenus-par-annee', [BatimentController::class, 'revenusParAnnee']);
        Route::get('{id}/occupants', [BatimentController::class, 'occupants']);
    });

    // Cartographie des éléments
    Route::prefix('cartographie-elements')->group(function () {
        Route::get('/', [CartographieElementController::class, 'index']);
        Route::post('/', [CartographieElementController::class, 'store']);
        Route::get('{id}', [CartographieElementController::class, 'show']);
        Route::put('{id}', [CartographieElementController::class, 'update']);
        Route::delete('{id}', [CartographieElementController::class, 'destroy']);
        Route::patch('{id}/position', [CartographieElementController::class, 'updatePosition']);
        Route::patch('{id}/dimensions', [CartographieElementController::class, 'updateDimensions']);
        Route::patch('{id}/rotation', [CartographieElementController::class, 'updateRotation']);
    });

    // Chambres
    Route::prefix('chambres')->group(function () {
        Route::get('/', [ChambreController::class, 'index']);
        Route::get('{id}', [ChambreController::class, 'show']);
        Route::post('{id}/verifier-disponibilite', [ChambreController::class, 'verifierDisponibilite']);
    });

    // Contrats
    Route::prefix('contrats')->group(function () {
        Route::get('/', [ContratController::class, 'index']);
        Route::get('{id}', [ContratController::class, 'show']);
        Route::post('/', [ContratController::class, 'store']);
        Route::put('{id}', [ContratController::class, 'update']);
        Route::delete('{id}', [ContratController::class, 'destroy']);
        Route::post('{id}/renouveler', [ContratController::class, 'renouveler']);
        Route::post('{id}/resilier', [ContratController::class, 'resilier']);
        Route::post('{id}/generer-facture', [ContratController::class, 'genererFacture']);
        Route::post('{id}/payer', [ContratController::class, 'payer']);
        Route::get('utilisateur/{id}', [ContratController::class, 'getContratsUtilisateur']);
        Route::get('utilisateur/{id}/active', [ContratController::class, 'getContratActif']);
    });

    // Critères d'évaluation
    Route::prefix('criteres-evaluation')->group(function () {
        Route::get('/', [CritereEvaluationController::class, 'index']);
        Route::get('{id}', [CritereEvaluationController::class, 'show']);
        Route::post('/', [CritereEvaluationController::class, 'store']);
        Route::put('{id}', [CritereEvaluationController::class, 'update']);
        Route::delete('{id}', [CritereEvaluationController::class, 'destroy']);
    });

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('dashboard/{id}', [DashboardController::class, 'show']);
    Route::get('dashboard/occupants/{id}', [DashboardController::class, 'getOccupants']);

    // Demandes d'affectation
    Route::prefix('demandes-affectation')->group(function () {
        Route::get('/', [DemandeAffectationController::class, 'index']);
        Route::get('{id}', [DemandeAffectationController::class, 'show']);
        Route::post('/', [DemandeAffectationController::class, 'store']);
        Route::put('{id}', [DemandeAffectationController::class, 'update']);
        Route::delete('{id}', [DemandeAffectationController::class, 'destroy']);
        Route::post('{id}/approuver', [DemandeAffectationController::class, 'approuver']);
        Route::post('{id}/refuser', [DemandeAffectationController::class, 'refuser']);
        Route::get('utilisateur/{id}', [DemandeAffectationController::class, 'getDemandesUtilisateur']);
        Route::get('statistiques', [DemandeAffectationController::class, 'getStatistiques']);  // <== ici sans /demandes-affectation/
    });

    // Enquête QHSE
    Route::prefix('enquete-qhse')->group(function () {
        Route::get('/', [EnqueteQHSEController::class, 'index']);
        Route::post('/', [EnqueteQHSEController::class, 'store']);
        Route::get('{id}', [EnqueteQHSEController::class, 'show']);
        Route::put('{id}', [EnqueteQHSEController::class, 'update']);
        Route::delete('{id}', [EnqueteQHSEController::class, 'destroy']);
    });

    // Equipements
    Route::prefix('equipements')->group(function () {
        Route::get('/', [EquipementController::class, 'index']);
        Route::post('/', [EquipementController::class, 'store']);
        Route::get('{id}', [EquipementController::class, 'show']);
        Route::put('{id}', [EquipementController::class, 'update']);
        Route::delete('{id}', [EquipementController::class, 'destroy']);
    });

    Route::prefix('maintenances')->group(function () {
    Route::get('/', [MaintenanceController::class, 'index']);            
    Route::get('{id}', [MaintenanceController::class, 'show']);           
    Route::post('/', [MaintenanceController::class, 'store']);           
    Route::put('{id}', [MaintenanceController::class, 'update']);        
    Route::delete('{id}', [MaintenanceController::class, 'destroy']);     

    Route::post('{id}/programmer', [MaintenanceController::class, 'programmer']);  
    Route::post('{id}/cloturer', [MaintenanceController::class, 'cloturer']);     
    });

    Route::prefix('reclamations')->group(function () {

    // Lister toutes les réclamations
    Route::get('/', [ReclamationController::class, 'index']);

    // Afficher une réclamation spécifique
    Route::get('/{id}', [ReclamationController::class, 'show']);

    // Créer une nouvelle réclamation
    Route::post('/', [ReclamationController::class, 'store']);

    // Mettre à jour une réclamation
    Route::put('/{id}', [ReclamationController::class, 'update']);

    // Supprimer une réclamation
    Route::delete('/{id}', [ReclamationController::class, 'destroy']);

    // Assigner un agent à une réclamation
    Route::post('/{id}/assigner', [ReclamationController::class, 'assignerAgent']);

    // Marquer une réclamation comme résolue
    Route::post('/{id}/resoudre', [ReclamationController::class, 'resoudre']);

    // Évaluer la satisfaction après résolution
    Route::post('/{id}/satisfaction', [ReclamationController::class, 'evaluerSatisfaction']);

    // Créer une maintenance liée à la réclamation
    Route::post('/{id}/maintenance', [ReclamationController::class, 'creerMaintenance']);

});


Route::prefix('locaux')->group(function () {

    // Liste tous les locaux
    Route::get('/', [LocalController::class, 'index']);

    // Crée un nouveau local
    Route::post('/', [LocalController::class, 'store']);

    // Affiche un local précis avec ses relations
    Route::get('/{id}', [LocalController::class, 'show']);

    // Met à jour un local
    Route::put('/{id}', [LocalController::class, 'update']);
    Route::patch('/{id}', [LocalController::class, 'update']);

    // Supprime un local
    Route::delete('/{id}', [LocalController::class, 'destroy']);

    // Vérifie si un local est occupé
    Route::get('/{id}/est-occupe', [LocalController::class, 'estOccupe']);

    // Vérifie la disponibilité du local entre deux dates
    Route::post('/{id}/verifier-disponibilite', [LocalController::class, 'veifierDisponibiliter']);

    // Récupère l'affectation active du local
    Route::get('/{id}/affectation-active', [LocalController::class, 'getAffectationActive']);

    // Vérifie la conformité du local
    Route::get('/{id}/verifier-conformite', [LocalController::class, 'verifierConformite']);

    // Récupère les chambres si le local est un pavillon
    Route::get('/{id}/chambres-pavillon', [LocalController::class, 'chambresDuPavillon']);

    // Récupère les locaux selon le type (ex: terrain, cantine, pavillon)
    Route::get('/type/{type}', [LocalController::class, 'locauxParType']);
});


Route::prefix('reservations')->group(function () {
    
    // Liste toutes les réservations
    Route::get('/', [ReservationController::class, 'index']);
    
    // Crée une nouvelle réservation
    Route::post('/', [ReservationController::class, 'store']);
    
    // Affiche une réservation spécifique
    Route::get('/{id}', [ReservationController::class, 'show']);
    
    // Met à jour une réservation
    Route::put('/{id}', [ReservationController::class, 'update']);
    Route::patch('/{id}', [ReservationController::class, 'update']);
    
    // Supprime une réservation
    Route::delete('/{id}', [ReservationController::class, 'destroy']);
    
    // Approuve une réservation
    Route::post('/{id}/approuver', [ReservationController::class, 'approuver']);
    
    // Annule une réservation (avec raison optionnelle dans le body)
    Route::post('/{id}/annuler', [ReservationController::class, 'annuler']);
});


Route::prefix('paiements')->group(function () {
    Route::get('/', [PaiementController::class, 'index']);                // Liste avec filtres + pagination
    Route::get('/{id}', [PaiementController::class, 'show']);             // Détails paiement
    Route::post('/', [PaiementController::class, 'store']);               // Créer un paiement
    Route::put('/{id}', [PaiementController::class, 'update']);           // Modifier un paiement
    Route::delete('/{id}', [PaiementController::class, 'destroy']);       // Supprimer un paiement

    Route::get('/etat', [PaiementController::class, 'getEtatPaiements']); // Résumé / état paiements
    Route::get('/retards', [PaiementController::class, 'getPaiementsEnRetard']); // Paiements en retard
    Route::get('/affectation/{affectationId}', [PaiementController::class, 'getPaiementsAffectation']); // Paiements d'une affectation

    Route::post('/{id}/valider', [PaiementController::class, 'validerPaiement']);   // Valider un paiement
    Route::post('/{id}/annuler', [PaiementController::class, 'annulerPaiement']);   // Annuler un paiement
    Route::get('/{id}/quittance', [PaiementController::class, 'genererQuittance']); // Générer quittance PDF
});


Route::prefix('rapports')->group(function () {
    Route::get('occupation', [RapportController::class, 'rapportOccupation']);
    Route::get('financier', [RapportController::class, 'rapportFinancier']);
    Route::get('demandes', [RapportController::class, 'rapportDemandes']);
});




