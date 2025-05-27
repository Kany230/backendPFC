<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Paiement;

class PaiementSeeder extends Seeder
{
    public function run(): void
    {
        Paiement::factory()->count(30)->create();
    }
}
