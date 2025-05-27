<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BatimentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => $this->faker->word() . ' Building',
            'adresse' => $this->faker->address(),
            'superficie' => $this->faker->randomFloat(2, 100, 10000),
            'description' => $this->faker->sentence(10),
            'dateConstruction' => $this->faker->date(),
            'localisation_lat' => $this->faker->latitude(),
            'localisation_lng' => $this->faker->longitude(),
        ];
    }
}
