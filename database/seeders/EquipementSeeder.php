<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipement;

class EquipementSeeder extends Seeder
{
    public function run(): void
    {
        Equipement::factory()->count(30)->create();
    }
}
