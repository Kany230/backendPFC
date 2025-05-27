<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Batiment;

class BatimentSeeder extends Seeder
{
    public function run(): void
    {
        // Crée 10 bâtiments fictifs
        Batiment::factory()->count(10)->create();
    }
}
