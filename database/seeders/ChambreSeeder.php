<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chambre;

class ChambreSeeder extends Seeder
{
    public function run(): void
    {
        Chambre::factory()->count(20)->create();
    }
}
