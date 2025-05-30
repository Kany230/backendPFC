<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des utilisateurs
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::post('/users/{user}/activate', [App\Http\Controllers\Admin\DashboardController::class, 'activateUser'])->name('users.activate');
    
    // Création rapide de gestionnaire
    Route::post('/gestionnaires', [App\Http\Controllers\Admin\DashboardController::class, 'storeGestionnaire'])->name('gestionnaires.store');
});

// Routes pour le gestionnaire
Route::middleware(['auth', 'role:gestionnaire'])->prefix('gestionnaire')->name('gestionnaire.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Gestionnaire\DashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des affectations
    Route::post('/affectations/{demande}/valider', [App\Http\Controllers\Gestionnaire\DashboardController::class, 'validerAffectation'])->name('affectations.valider');
    Route::post('/affectations/{demande}/refuser', [App\Http\Controllers\Gestionnaire\DashboardController::class, 'refuserAffectation'])->name('affectations.refuser');
    
    // Gestion des réclamations
    Route::post('/reclamations/{reclamation}/assign', [App\Http\Controllers\Gestionnaire\DashboardController::class, 'assignerReclamation'])->name('reclamations.assign');
    Route::resource('reclamations', App\Http\Controllers\Gestionnaire\ReclamationController::class);
    Route::resource('rapports', App\Http\Controllers\Gestionnaire\RapportController::class);
});

// Routes pour le chef de pavillon
Route::middleware(['auth', 'role:chef_pavillon'])->prefix('chef-pavillon')->name('chef-pavillon.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\ChefPavillon\DashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des affectations de chambre
    Route::post('/affectations/{demande}/valider', [App\Http\Controllers\ChefPavillon\DashboardController::class, 'validerAffectation'])->name('affectations.valider');
    Route::post('/affectations/{demande}/refuser', [App\Http\Controllers\ChefPavillon\DashboardController::class, 'refuserAffectation'])->name('affectations.refuser');
    
    // Gestion des réclamations
    Route::post('/reclamations/{reclamation}/assign', [App\Http\Controllers\ChefPavillon\DashboardController::class, 'assignerReclamation'])->name('reclamations.assign');
    Route::resource('reclamations', App\Http\Controllers\ChefPavillon\ReclamationController::class);
    Route::resource('etudiants', App\Http\Controllers\ChefPavillon\EtudiantController::class);
});

// Routes pour les agents QHSE
Route::middleware(['auth', 'role:qhse'])->prefix('qhse')->name('qhse.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\QHSE\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/rapports', [App\Http\Controllers\QHSE\DashboardController::class, 'storeRapport'])->name('rapports.store');
    Route::get('/rapports/{rapport}', [App\Http\Controllers\QHSE\DashboardController::class, 'showRapport'])->name('rapports.show');
});

// Routes pour les étudiants
Route::middleware(['auth', 'role:etudiant'])->prefix('etudiant')->name('etudiant.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Etudiant\DashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des réservations et contrats
    Route::get('/contrat/{reservation}/download', [App\Http\Controllers\Etudiant\DashboardController::class, 'downloadContrat'])->name('contrat.download');
    
    // Gestion des paiements
    Route::post('/paiements', [App\Http\Controllers\Etudiant\DashboardController::class, 'storePaiement'])->name('paiements.store');
    Route::get('/paiements/{paiement}', [App\Http\Controllers\Etudiant\DashboardController::class, 'showPaiement'])->name('paiements.show');
    
    // Gestion des réclamations
    Route::post('/reclamations', [App\Http\Controllers\Etudiant\DashboardController::class, 'storeReclamation'])->name('reclamations.store');
    Route::get('/reclamations/{reclamation}', [App\Http\Controllers\Etudiant\DashboardController::class, 'showReclamation'])->name('reclamations.show');
});

// Routes pour les commerçants
Route::middleware(['auth', 'role:commercant'])->prefix('commercant')->name('commercant.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Commercant\DashboardController::class, 'index'])->name('dashboard');
    
    // Gestion des réservations et contrats
    Route::get('/contrat/{reservation}/download', [App\Http\Controllers\Commercant\DashboardController::class, 'downloadContrat'])->name('contrat.download');
    
    // Gestion des paiements
    Route::post('/paiements', [App\Http\Controllers\Commercant\DashboardController::class, 'storePaiement'])->name('paiements.store');
    Route::get('/paiements/{paiement}', [App\Http\Controllers\Commercant\DashboardController::class, 'showPaiement'])->name('paiements.show');
    
    // Gestion des réclamations
    Route::post('/reclamations', [App\Http\Controllers\Commercant\DashboardController::class, 'storeReclamation'])->name('reclamations.store');
    Route::get('/reclamations/{reclamation}', [App\Http\Controllers\Commercant\DashboardController::class, 'showReclamation'])->name('reclamations.show');
    
    // Notification par email
    Route::post('/reservations/{reservation}/notify', [App\Http\Controllers\Commercant\DashboardController::class, 'notifyReservationAcceptee'])->name('reservations.notify');
});

// Routes pour la gestion des techniciens (accessibles aux gestionnaires et chefs de pavillon)
Route::middleware(['auth'])->group(function () {
    Route::resource('techniciens', App\Http\Controllers\TechnicienController::class);
});

// Routes pour les techniciens
Route::middleware(['auth', 'role:technicien'])->prefix('technicien')->name('technicien.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Technicien\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reclamations/{reclamation}', [App\Http\Controllers\Technicien\DashboardController::class, 'showReclamation'])->name('reclamations.show');
    Route::post('/reclamations/{reclamation}/terminer', [App\Http\Controllers\Technicien\DashboardController::class, 'terminerReclamation'])->name('reclamations.terminer');
});

// Route API pour la cartographie
Route::get('/api/batiments', [App\Http\Controllers\BatimentController::class, 'getBatimentsForMap']);

// Route pour la génération de factures
Route::get('/factures/{paiement}/download', [App\Http\Controllers\FactureController::class, 'genererFacture'])
    ->name('factures.download')
    ->middleware('auth');
