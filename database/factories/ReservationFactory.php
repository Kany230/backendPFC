<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Local;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $dateDebut = $this->faker->dateTimeBetween('now', '+1 month');
        $dateFin = $this->faker->optional()->dateTimeBetween($dateDebut, '+2 months');

        return [
            'id_local' => Local::inRandomOrder()->first()?->id ?? Local::factory(),
            'id_utilisateur' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'statut' => $this->faker->randomElement(['En attente', 'Approuvée', 'Rejetée', 'Annulée']),
            'motif' => $this->faker->sentence(6),
            'remarques' => $this->faker->optional()->paragraph(),
        ];
    }
}
