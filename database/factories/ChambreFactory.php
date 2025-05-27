<?php

namespace Database\Factories;

use App\Models\Chambre;
use App\Models\Local;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChambreFactory extends Factory
{
    protected $model = Chambre::class;

    public function definition(): array
    {
        return [
            'id_local' => Local::inRandomOrder()->first()?->id ?? Local::factory(),
            'id_utilisateur' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'numero' => $this->faker->unique()->bothify('CH###'),  // ex : CH123
            'superficie' => $this->faker->optional()->randomFloat(2, 10, 50), // entre 10 et 50 m²
            'capacite' => $this->faker->numberBetween(1, 4),  // capacité entre 1 et 4
        ];
    }
}
