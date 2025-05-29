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
        Schema::create('chambres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_local')->constrained('locaux')->onDelete('cascade');
            $table->foreignId('id_utilisateur')->constrained('users')->onDelete('cascade');
            $table->string('numero');
            $table->decimal('superficie', 8, 2)->nullable();
            $table->integer('capacite')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chambres');
    }
};
