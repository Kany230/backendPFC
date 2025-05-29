<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\BatimentController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\AlerteController;
use App\Http\Controllers\ReclamationController;
use App\Http\Controllers\CartographieElementController;
use App\Http\Controllers\CalqueController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemandeAffectationController;
use App\Http\Controllers\EnqueteQHSEController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GestionnaireController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use Illuminate\Http\Request;
use App\Http\Controllers\CartographieController;

// Sanctum CSRF Cookie
Route::get('sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

// Routes publiques
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'reinitialiserPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Routes protégées
Route::middleware(['auth:sanctum'])->group(function () {
    // Route utilisateur
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Routes Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/utilisateurs/en-attente', [AdminController::class, 'listeEnAttente']);
        Route::post('/utilisateurs/{id}/valider', [AdminController::class, 'validerInscription']);
        Route::post('/gestionnaires/ajouter', [AdminController::class, 'ajouterGestionnaire']);
    });

    // Routes Chef de Pavillon
    Route::middleware('role:chefpavillon')->group(function () {
        // Ajouter les routes chef de pavillon ici
    });

    // Routes Gestionnaire
    Route::middleware('role:gestionnaire')->group(function () {
        Route::post('/personnel/ajouter', [GestionnaireController::class, 'ajouterPersonnel']);
        Route::get('/personnel', [GestionnaireController::class, 'getPersonnel']);
        Route::get('/personnel/chefs-pavillon', [GestionnaireController::class, 'getChefsPavillon']);
        Route::get('/personnel/agents-qhse', [GestionnaireController::class, 'getAgentsQHSE']);
        Route::get('/personnel/techniciens', [GestionnaireController::class, 'getTechniciens']);
    });

    // Routes Commerçant
    Route::middleware('role:commercant')->group(function () {
        // Ajouter les routes commerçant ici
    });

    // Routes Technicien
    Route::middleware('role:technicien')->group(function () {
        // Ajouter les routes technicien ici
    });

    // Routes Agent QHSE
    Route::middleware('role:agentQHSE')->group(function () {
        // Ajouter les routes agent QHSE ici
    });

    // Routes Étudiant
    Route::middleware('role:etudiant')->group(function () {
        // Ajouter les routes étudiant ici
    });

    // Profil
    Route::get('profil', [UsersController::class, 'getProfil']);
    Route::put('profil', [UsersController::class, 'updateProfil']);

    // Utilisateurs
    Route::apiResource('utilisateurs', UsersController::class);
    Route::patch('utilisateurs/{id}/changer-statut', [UsersController::class, 'changerStatut']);

    // Routes pour la cartographie
    Route::prefix('cartographie')->group(function () {
        // Gestion des éléments cartographiques
        Route::get('elements', [CartographieController::class, 'index']);
        Route::get('elements/{id}', [CartographieController::class, 'show']);
        Route::middleware(['role:admin,gestionnaire'])->group(function () {
            Route::post('elements', [CartographieController::class, 'store']);
            Route::put('elements/{id}', [CartographieController::class, 'update']);
            Route::delete('elements/{id}', [CartographieController::class, 'destroy']);
            Route::put('elements/ordre', [CartographieController::class, 'updateOrdre']);
            Route::put('elements/{id}/visibilite', [CartographieController::class, 'toggleVisibilite']);
        });
        Route::get('calque/{calqueId}/elements', [CartographieController::class, 'getParCalque']);

        // Gestion des calques
        Route::get('calques', [CalqueController::class, 'index']);
        Route::get('calques/{id}', [CalqueController::class, 'show']);
        Route::middleware(['role:admin,gestionnaire'])->group(function () {
            Route::post('calques', [CalqueController::class, 'store']);
            Route::put('calques/{id}', [CalqueController::class, 'update']);
            Route::delete('calques/{id}', [CalqueController::class, 'destroy']);
            Route::put('calques/ordre', [CalqueController::class, 'updateOrdre']);
            Route::put('calques/{id}/visibilite', [CalqueController::class, 'toggleVisibilite']);
        });
        Route::get('calques/{id}/elements', [CalqueController::class, 'getElements']);
    });

    // Routes pour les locaux
    Route::prefix('locaux')->group(function () {
        Route::get('/', [LocalController::class, 'index']);
        Route::get('/{id}', [LocalController::class, 'show']);
        Route::middleware(['role:admin,gestionnaire'])->group(function () {
            Route::post('/', [LocalController::class, 'store']);
            Route::put('/{id}', [LocalController::class, 'update']);
            Route::delete('/{id}', [LocalController::class, 'destroy']);
        });
        Route::get('/{id}/est-occupe', [LocalController::class, 'estOccupe']);
        Route::post('/{id}/verifier-disponibilite', [LocalController::class, 'verifierDisponibilite']);
        Route::get('/{id}/affectation-active', [LocalController::class, 'getAffectationActive']);
        Route::get('/{id}/verifier-conformite', [LocalController::class, 'verifierConformite']);
        Route::get('/type/{type}', [LocalController::class, 'locauxParType']);
        Route::get('/{id}/chambres', [LocalController::class, 'chambresDuPavillon']);
    });

    // Routes pour les bâtiments
    Route::prefix('batiments')->group(function () {
        Route::get('/', [BatimentController::class, 'index']);
        Route::get('/{id}', [BatimentController::class, 'show']);
        Route::middleware(['role:admin,gestionnaire'])->group(function () {
            Route::post('/', [BatimentController::class, 'store']);
            Route::put('/{id}', [BatimentController::class, 'update']);
            Route::delete('/{id}', [BatimentController::class, 'destroy']);
        });
        Route::get('/{id}/occupants', [BatimentController::class, 'occupants']);
        Route::get('/{id}/revenus', [BatimentController::class, 'getRevenus']);
        Route::get('/{id}/revenus/annee', [BatimentController::class, 'revenusParAnnee']);
        Route::get('/{id}/revenus/mois/{annee?}', [BatimentController::class, 'revenusParMois']);
        Route::get('/{id}/statistiques', [BatimentController::class, 'getStatistiques']);
    });

    // Routes pour les réclamations
    Route::prefix('reclamations')->group(function () {
        Route::get('/', [ReclamationController::class, 'index']);
        Route::get('/{id}', [ReclamationController::class, 'show']);
        Route::post('/', [ReclamationController::class, 'store']);
        Route::middleware(['role:admin,gestionnaire,agent'])->group(function () {
            Route::put('/{id}', [ReclamationController::class, 'update']);
            Route::delete('/{id}', [ReclamationController::class, 'destroy']);
            Route::post('/{id}/assigner', [ReclamationController::class, 'assignerAgent']);
            Route::post('/{id}/resoudre', [ReclamationController::class, 'resoudre']);
        });
        Route::post('/{id}/evaluer', [ReclamationController::class, 'evaluerSatisfaction']);
    });

    // Routes pour les maintenances
    Route::prefix('maintenances')->middleware(['role:admin,gestionnaire,agent'])->group(function () {
        Route::get('/', [MaintenanceController::class, 'index']);
        Route::get('/{id}', [MaintenanceController::class, 'show']);
        Route::post('/', [MaintenanceController::class, 'store']);
        Route::put('/{id}', [MaintenanceController::class, 'update']);
        Route::delete('/{id}', [MaintenanceController::class, 'destroy']);
        Route::post('/{id}/demarrer', [MaintenanceController::class, 'demarrer']);
        Route::post('/{id}/terminer', [MaintenanceController::class, 'terminer']);
        Route::post('/{id}/annuler', [MaintenanceController::class, 'annuler']);
    });

    // Routes pour les affectations
    Route::prefix('affectations')->group(function () {
        Route::get('/', [AffectationController::class, 'index']);
        Route::get('/{id}', [AffectationController::class, 'show']);
        Route::post('/', [AffectationController::class, 'store']);
        Route::middleware(['role:admin,gestionnaire'])->group(function () {
            Route::put('/{id}', [AffectationController::class, 'update']);
            Route::delete('/{id}', [AffectationController::class, 'destroy']);
            Route::post('/{id}/valider', [AffectationController::class, 'valider']);
            Route::post('/{id}/refuser', [AffectationController::class, 'refuser']);
        });
        Route::get('/utilisateur/{userId}', [AffectationController::class, 'getAffectationsUtilisateur']);
        Route::get('/local/{localId}', [AffectationController::class, 'getAffectationsLocal']);
    });

    // Paiements
    Route::apiResource('paiements', PaiementController::class);
    Route::get('paiements/affectation/{affectationId}', [PaiementController::class, 'getPaiementsAffectation']);
    Route::get('paiements/etat', [PaiementController::class, 'getEtatPaiements']);
    Route::get('paiements/retards', [PaiementController::class, 'getPaiementsEnRetard']);
    Route::post('paiements/{id}/valider', [PaiementController::class, 'validerPaiement']);
    Route::post('paiements/{id}/annuler', [PaiementController::class, 'annulerPaiement']);
    Route::get('paiements/{id}/quittance', [PaiementController::class, 'genererQuittance']);

    // Réservations
    Route::apiResource('reservations', ReservationController::class);
    Route::post('reservations/{id}/approuver', [ReservationController::class, 'approuver']);
    Route::post('reservations/{id}/annuler', [ReservationController::class, 'annuler']);

    // Alertes
    Route::apiResource('alertes', AlerteController::class);
    Route::get('alertes/non-lues/nombre', [AlerteController::class, 'getNombreNonLues']);
    Route::patch('alertes/{id}/lue', [AlerteController::class, 'marquerCommeLue']);
    Route::patch('alertes/toutes-lues', [AlerteController::class, 'marquerToutesCommeLues']);

    // Réclamations
    Route::apiResource('reclamations', ReclamationController::class);
    Route::get('reclamations/etudiants-avec-reclamations', [ReclamationController::class, 'getEtudiantsAvecReclamations']);
    Route::get('reclamations/etudiants-avec-reclamations-simple', [ReclamationController::class, 'getEtudiantsAvecReclamationsSimple']);
    Route::post('reclamations/{id}/maintenance', [ReclamationController::class, 'creerMaintenance']);
    Route::post('reclamations/{id}/satisfaction', [ReclamationController::class, 'evaluerSatisfaction']);

    // Cartographie
    Route::apiResource('cartographie-elements', CartographieElementController::class);
    Route::patch('cartographie-elements/{id}/position', [CartographieElementController::class, 'updatePosition']);
    Route::patch('cartographie-elements/{id}/dimensions', [CartographieElementController::class, 'updateDimensions']);
    Route::patch('cartographie-elements/{id}/rotation', [CartographieElementController::class, 'updateRotation']);

    // Calques
    Route::apiResource('calques', CalqueController::class);
    Route::patch('calques/{calque}/visibility', [CalqueController::class, 'updateVisibility']);
    Route::patch('calques/{calque}/ordre', [CalqueController::class, 'updateOrdre']);
    Route::post('calques/{calque}/elements/{element}', [CalqueController::class, 'addElement']);
    Route::delete('calques/{calque}/elements/{element}', [CalqueController::class, 'removeElement']);

    // Dashboard
    Route::apiResource('dashboard', DashboardController::class);
    Route::get('dashboard/occupants/{id}', [DashboardController::class, 'getOccupants']);

    // Demandes d'affectation
    Route::apiResource('demandes-affectation', DemandeAffectationController::class);
    Route::get('demandes-affectation/statistiques', [DemandeAffectationController::class, 'getStatistiques']);
    Route::get('demandes-affectation/utilisateur/{id}', [DemandeAffectationController::class, 'getDemandesUtilisateur']);
    Route::post('demandes-affectation/{id}/approuver', [DemandeAffectationController::class, 'approuver']);
    Route::post('demandes-affectation/{id}/refuser', [DemandeAffectationController::class, 'refuser']);

    // Enquêtes QHSE
    Route::apiResource('enquete-qhse', EnqueteQHSEController::class);

    // Rapports
    Route::get('rapports/occupation', [RapportController::class, 'rapportOccupation']);
    Route::get('rapports/financier', [RapportController::class, 'rapportFinancier']);
    Route::get('rapports/demandes', [RapportController::class, 'rapportDemandes']);
});

// Routes pour l'authentification
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



