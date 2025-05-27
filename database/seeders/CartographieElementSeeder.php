<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CartographieElement;

class CartographieElementSeeder extends Seeder
{
    public function run(): void
    {
        CartographieElement::factory()->count(30)->create();
    }
}
