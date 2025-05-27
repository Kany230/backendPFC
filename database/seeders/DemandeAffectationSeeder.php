<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DemandeAffectation;

class DemandeAffectationSeeder extends Seeder
{
    public function run(): void
    {
        DemandeAffectation::factory()->count(30)->create();
    }
}
