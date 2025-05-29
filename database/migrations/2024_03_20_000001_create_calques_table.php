<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('calques', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('description')->nullable();
            $table->boolean('visible')->default(true);
            $table->integer('ordre')->default(0);
            $table->timestamps();
        });

        // Ajouter la colonne calque_id à la table cartographie_elements
        Schema::table('cartographie_elements', function (Blueprint $table) {
            $table->foreignId('calque_id')->nullable()->constrained()->onDelete('set null');
            // Renommer les colonnes pour suivre les conventions Laravel
            $table->renameColumn('id_batiment', 'batiment_id');
            $table->renameColumn('id_local', 'local_id');
        });
    }

    public function down()
    {
        Schema::table('cartographie_elements', function (Blueprint $table) {
            $table->dropForeign(['calque_id']);
            $table->dropColumn('calque_id');
            $table->renameColumn('batiment_id', 'id_batiment');
            $table->renameColumn('local_id', 'id_local');
        });

        Schema::dropIfExists('calques');
    }
}; 