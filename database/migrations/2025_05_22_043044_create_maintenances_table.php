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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_equipement')->constrained('equipements')->onDelete('cascade');
            $table->enum('type', ['Préventive', 'Corrective', 'Urgente']);
            $table->text('description');
            $table->enum('priorite', ['Faible', 'Normale', 'Élevée', 'Urgente'])->default('Normale');
            $table->timestamp('dateSignalement')->useCurrent();
            $table->timestamp('dateDebut')->nullable();
            $table->timestamp('dateFin')->nullable();
            $table->enum('statut', ['Signalée', 'Programmée', 'En cours', 'Terminée', 'Annulée'])->default('Signalée');
            $table->decimal('cout', 10, 2)->nullable();
            $table->text('remarques')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['id_equipement', 'statut']);
            $table->index(['dateSignalement', 'priorite']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
