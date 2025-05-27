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
        Schema::create('alertes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['Maintenance', 'Contrat', 'Paiement', 'Système']);
            $table->unsignedBigInteger('id_source')->nullable();
            $table->text('message');
            $table->timestamp('dateCreation')->useCurrent();
            $table->timestamp('dateEcheance')->nullable();
            $table->enum('priorite', ['Faible', 'Moyenne', 'Élevée', 'Critique'])->default('Moyenne');
            $table->boolean('vue')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['type', 'id_source']);
            $table->index(['vue', 'priorite']);
            $table->index(['dateEcheance', 'vue']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertes');
    }
};
