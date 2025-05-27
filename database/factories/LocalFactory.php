<?php

namespace Database\Factories;

use App\Models\Batiment;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => $this->faker->unique()->word(),
            'id_batiment' => Batiment::factory(), // génère un batiment associé si nécessaire
            'type' => $this->faker->randomElement(['Cantine', 'Espace à louer', 'Pavillon']),
            'superficie' => $this->faker->randomFloat(2, 20, 200),
            'capacite' => $this->faker->numberBetween(1, 100),
            'etage' => $this->faker->numberBetween(0, 5),
            'disponible' => $this->faker->boolean(),
            'statut_conformite' => $this->faker->randomElement(['Conforme', 'Non conforme', 'En attente']),
            'description' => $this->faker->sentence(),
        ];
    }
}
