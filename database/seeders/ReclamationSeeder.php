<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Reclamation;

class ReclamationSeeder extends Seeder
{
    public function run(): void
    {
        Reclamation::factory()->count(30)->create();
    }
}
