<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Affectation;

class AffectationSeeder extends Seeder
{
    public function run(): void
    {
        Affectation::factory()->count(30)->create();
    }
}
