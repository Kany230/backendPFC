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
        Schema::create('equipements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_local')->constrained('locaux')->onDelete('cascade');
            $table->string('nom');
            $table->enum('type', ['Mobilier', 'Électroménager', 'Informatique', 'Chauffage', 'Plomberie', 'Électricité', 'Autre']);
            $table->string('numeroSerie')->nullable();
            $table->date('dateAcquisition')->nullable();
            $table->date('dateFinGarantie')->nullable();
            $table->enum('etat', ['Neuf', 'Bon', 'Usé', 'Défaillant', 'Hors service']);
            $table->decimal('valeur', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['id_local', 'type', 'etat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipements');
    }
};
