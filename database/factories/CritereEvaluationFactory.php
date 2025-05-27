<?php

namespace Database\Factories;

use App\Models\EnqueteQHSE;
use Illuminate\Database\Eloquent\Factories\Factory;

class CritereEvaluationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_enquete_qhse' => EnqueteQHSE::inRandomOrder()->first()?->id ?? EnqueteQHSE::factory(),
            'categorie' => $this->faker->randomElement(['Securite', 'Hygiene', 'Qualite', 'Environnement']),
            'decription' => $this->faker->sentence(10),
            'conforme' => $this->faker->boolean(80),
            'observation' => $this->faker->optional()->sentence(),
            'priorite' => $this->faker->randomElement(['Faible', 'Moyenne', 'Elevee', 'Critique']),
        ];
    }
}
