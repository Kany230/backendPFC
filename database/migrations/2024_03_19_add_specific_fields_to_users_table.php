<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Champs spécifiques aux étudiants
            $table->enum('niveau_etude', ['licence1', 'licence2', 'licence3', 'master1', 'master2', 'doctorat'])->nullable();
            $table->string('filiere')->nullable();
            $table->string('numero_carte_etudiant')->nullable();
            
            // Champs spécifiques aux commerçants
            $table->string('numero_cni')->nullable();
            $table->string('cv_url')->nullable();
            $table->enum('type_commerce', ['restaurant', 'cantine', 'commerce'])->nullable();
            
            // Champs communs pour la réservation
            $table->enum('demande_status', ['en_attente', 'approuvee', 'refusee'])->nullable();
            $table->enum('type_reservation', ['chambre', 'espace_commercial'])->nullable();
            $table->timestamp('date_demande')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'niveau_etude',
                'filiere',
                'numero_carte_etudiant',
                'numero_cni',
                'cv_url',
                'type_commerce',
                'demande_status',
                'type_reservation',
                'date_demande'
            ]);
        });
    }
}; 