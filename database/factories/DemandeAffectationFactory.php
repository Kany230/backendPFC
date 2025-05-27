<?php

namespace Database\Factories;

use App\Models\Local;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DemandeAffectationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_local' => Local::inRandomOrder()->first()?->id ?? Local::factory(),
            'id_utilisateur' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'dateCreation' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'statut' => $this->faker->randomElement(['En attente', 'En cours', 'Approuvee', 'Refusee']),
            'typeOccuptation' => $this->faker->randomElement(['Temporarire', 'Permanent', 'Saisoniere']),
            'avisqhse' => $this->faker->boolean(50),
            'validationGestionnaire' => $this->faker->boolean(50),
        ];
    }
}
