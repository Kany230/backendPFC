<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EnqueteQhse;

class EnqueteQhseSeeder extends Seeder
{
    public function run(): void
    {
        EnqueteQhse::factory()->count(20)->create();
    }
}
