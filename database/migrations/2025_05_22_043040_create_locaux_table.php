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
        Schema::create('locaux', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->foreignId('id_batiment')->constrained('batiments')->onDelete('cascade');
            $table->enum('type', ['Cantine', 'Espace à louer', 'Pavillon']);
            $table->decimal('superficie', 8, 2)->nullable();
            $table->integer('capacite')->default(1);
            $table->integer('etage')->nullable();
            $table->boolean('disponible')->default(true);
            $table->enum('statut_conformite', ['Conforme', 'Non conforme', 'En attente'])->default('En attente');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['id_batiment', 'type', 'disponible']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locaux');
    }
};
