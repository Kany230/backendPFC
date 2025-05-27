<?php

namespace Database\Factories;

use App\Models\Local;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipementFactory extends Factory
{
    public function definition(): array
    {
        $dateAcquisition = $this->faker->dateTimeBetween('-5 years', 'now');
        $dateFinGarantie = (clone $dateAcquisition)->modify('+'.rand(1, 3).' years');

        return [
            'id_local' => Local::inRandomOrder()->first()?->id ?? Local::factory(),
            'nom' => $this->faker->word(),
            'type' => $this->faker->randomElement(['Mobilier', 'Électroménager', 'Informatique', 'Chauffage', 'Plomberie', 'Électricité', 'Autre']),
            'numeroSerie' => $this->faker->optional()->bothify('SN-###-??'),
            'dateAcquisition' => $dateAcquisition,
            'dateFinGarantie' => $dateFinGarantie,
            'etat' => $this->faker->randomElement(['Neuf', 'Bon', 'Usé', 'Défaillant', 'Hors service']),
            'valeur' => $this->faker->optional()->randomFloat(2, 100, 10000),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
