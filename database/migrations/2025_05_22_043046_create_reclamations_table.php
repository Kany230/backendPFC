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
        Schema::create('reclamations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_utilisateur')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_local')->constrained('locaux')->onDelete('cascade');
            $table->foreignId('id_agent')->nullable()->constrained('users')->onDelete('set null');
            $table->string('objet');
            $table->text('description');
            $table->timestamp('dateCreation')->useCurrent();
            $table->enum('statut', ['Ouverte', 'Assignée', 'En cours', 'Résolue', 'Fermée'])->default('Ouverte');
            $table->enum('priorite', ['Faible', 'Normale', 'Élevée', 'Urgente'])->default('Normale');
            $table->timestamp('dateResolution')->nullable();
            $table->text('solution')->nullable();
            $table->integer('satisfaction')->nullable()->comment('Note de satisfaction de 1 à 5');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['id_utilisateur', 'statut']);
            $table->index(['id_local', 'statut']);
            $table->index(['id_agent', 'statut']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reclamations');
    }
};
