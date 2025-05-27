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
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_affectation')->constrained('affectations')->onDelete('cascade');
            $table->string('reference')->unique();
            $table->date('dateDebut');
            $table->date('dateFin');
            $table->decimal('montant', 10, 2);
            $table->enum('frequence_paiement', ['Mensuel', 'Trimestriel', 'Semestriel', 'Annuel']);
            $table->enum('type', ['Location', 'Sous-location', 'Convention']);
            $table->enum('statut', ['Actif', 'Expiré', 'Résilié'])->default('Actif');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['reference', 'statut']);
            $table->index(['dateDebut', 'dateFin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
