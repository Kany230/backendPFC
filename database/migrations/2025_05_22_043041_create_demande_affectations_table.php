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
        Schema::create('demande_affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_local')->constrained('locaux')->onDelete('cascade');
            $table->foreignId('id_utilisateur')->constrained('users')->onDelete('cascade');
            $table->timestamp('dateCreation')->useCurrent();
            $table->enum('statut', ['En attente', 'En cours', 'Approuvee', 'Refusee'])->default('En attente');
            $table->enum('typeOccuptation', ['Temporarire', 'Permanent', 'Saisoniere']);
            $table->boolean('avisqhse')->default(false);
            $table->boolean('validationGestionnaire')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['id_utilisateur', 'statut']);
            $table->index(['id_local', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demande_affectations');
    }
};
