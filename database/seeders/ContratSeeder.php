<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contrat;

class ContratSeeder extends Seeder
{
    public function run(): void
    {
        Contrat::factory()->count(20)->create();
    }
}
