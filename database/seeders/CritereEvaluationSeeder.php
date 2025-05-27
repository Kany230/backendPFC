<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CritereEvaluation;

class CritereEvaluationSeeder extends Seeder
{
    public function run(): void
    {
        CritereEvaluation::factory()->count(50)->create();
    }
}
