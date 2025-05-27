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
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_utilisateur')->constrained('users')->onDelete('cascade');
             $table->foreignId('id_affectation')->constrained('affectations')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->timestamp('datePaiement')->nullable();
            $table->date('dateEcheance');
            $table->enum('methode_paiement', ['Wave', 'Orange Money', 'Espèces',  'Autre'])->nullable();
            $table->string('reference')->nullable();
            $table->enum('statut', ['En attente', 'Validé', 'Annulé', 'En retard'])->default('En attente');
            $table->text('enregistre_par');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['id_affectation', 'statut']);
            $table->index(['dateEcheance', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};
