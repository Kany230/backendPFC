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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_local')->constrained('locaux')->onDelete('cascade');
            $table->foreignId('id_utilisateur')->constrained('users')->onDelete('cascade');
            $table->timestamp('dateDebut');
            $table->timestamp('dateFin')->nullable();
            $table->enum('statut', ['En attente', 'Approuvée', 'Rejetée', 'Annulée'])->default('En attente');
            $table->text('motif');
            $table->text('remarques')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['id_local', 'statut']);
            $table->index(['id_utilisateur', 'statut']);
            $table->index(['dateDebut', 'dateFin']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
