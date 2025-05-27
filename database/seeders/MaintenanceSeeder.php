<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Maintenance;

class MaintenanceSeeder extends Seeder
{
    public function run(): void
    {
        Maintenance::factory()->count(30)->create();
    }
}
