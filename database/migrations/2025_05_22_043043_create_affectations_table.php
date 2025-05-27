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
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_demande_affectation')->constrained('demande_affectations')->onDelete('cascade');
            $table->foreignId('id_local')->constrained('locaux')->onDelete('cascade');
            $table->foreignId('id_utilisateur')->constrained('users')->onDelete('cascade');
            $table->date('dateDebut');
            $table->date('dateFin');
            $table->enum('type', ['Temporaire', 'Permanente', 'Saisonniere']);
            $table->enum('statut', ['Active', 'Expire', 'Resiliee'])->default('Active');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['id_utilisateur', 'statut']);
            $table->index(['id_local', 'statut']);
            $table->index(['dateDebut', 'dateFin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
