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
        Schema::create('enquete_qhse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_demande_affectation')->constrained('demande_affectations')->onDelete('cascade');
            $table->foreignId('id_local')->constrained('locaux')->onDelete('cascade');
            $table->foreignId('id_agent_qhse')->constrained('users')->onDelete('cascade');
            $table->timestamp('dateDebut')->nullable();
            $table->timestamp('dateFin')->nullable();
            $table->enum('statut', ['En_cours', 'En attente', 'Terminee']);
            $table->text('conclusion')->nullable();
            $table->boolean('conforme')->default(false);
            $table->softDeletes();
            $table->index(['id_agent_qhse', 'statut']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquete_qhse');
    }
};
