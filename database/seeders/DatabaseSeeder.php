<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            LocalSeeder::class,
            AffectationSeeder::class,
            ContratSeeder::class,
            PaiementSeeder::class,
            MaintenanceSeeder::class,
            ReservationSeeder::class,
            AlerteSeeder::class,
            ReclamationSeeder::class,
            ChambreSeeder::class,
            CartographieElementSeeder::class,
        ]);
    }
}
