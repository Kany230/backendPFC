<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function PHPSTORM_META\type;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('batiments', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->text('adresse');
            $table->decimal('superficie', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->date('dateConstruction')->nullable();
            $table->decimal('localisation_lat', 10, 8)->nullable();
            $table->decimal('localisation_lng', 11, 8)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batiments');
    }
};
