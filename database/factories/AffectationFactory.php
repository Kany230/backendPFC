<?php

namespace Database\Factories;

use App\Models\Chambre;
use App\Models\User;
use App\Models\Local;
use App\Models\DemandeAffectation;
use Illuminate\Database\Eloquent\Factories\Factory;

class AffectationFactory extends Factory
{
    public function definition(): array
    {
        $dateDebut = $this->faker->dateTimeBetween('-1 year', 'now');
        $dateFin = (clone $dateDebut)->modify('+6 months');

        return [
            'id_demande_affectation' => DemandeAffectation::inRandomOrder()->first()?->id ?? DemandeAffectation::factory(),
            'id_local' => Local::inRandomOrder()->first()?->id ?? Local::factory(),
            'id_utilisateur' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'id_chambre' => Chambre::inRandomOrder()->first()?->id ?? Chambre::factory(),
            'dateDebut' => $dateDebut->format('Y-m-d'),
            'dateFin' => $dateFin->format('Y-m-d'),
            'type' => $this->faker->randomElement(['Temporaire', 'Permanente', 'Saisonniere']),
            'statut' => $this->faker->randomElement(['Active', 'Expire', 'Resiliee']),
        ];
    }
}
