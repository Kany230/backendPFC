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
        Schema::create('critere_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_enquete_qhse')->constrained('enquete_qhse')->onDelete('cascade');
            $table->enum('categorie', ['Securite', 'Hygiene', 'Qualite', 'Environnement']);
            $table->text('decription');
            $table->boolean('conforme')->default(false);
            $table->text('observation')->nullable();
            $table->enum('priorite', ['Faible', 'Moyenne', 'Elevee', 'Critique'])->default('Moyenne');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('critere_evaluations');
    }
};
