<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Local;

class LocalSeeder extends Seeder
{
    public function run(): void
    {
        // Crée 20 locaux, chacun associé à un bâtiment (via factory)
        Local::factory()->count(10)->create();
    }
}
