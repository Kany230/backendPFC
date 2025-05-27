<?php

namespace Database\Factories;

use App\Models\DemandeAffectation;
use App\Models\Local;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnqueteQhseFactory extends Factory
{
    public function definition(): array
    {
        $dateDebut = $this->faker->dateTimeBetween('-3 months', '-1 month');
        $dateFin = (clone $dateDebut)->modify('+'.rand(1, 10).' days');

        return [
            'id_demande_affectation' => DemandeAffectation::inRandomOrder()->first()?->id ?? DemandeAffectation::factory(),
            'id_local' => Local::inRandomOrder()->first()?->id ?? Local::factory(),
            'id_agent_qhse' => User::where('role', 'agentQHSE')->inRandomOrder()->first()?->id ?? User::factory()->create(['role' => 'agentQHSE'])->id,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'statut' => $this->faker->randomElement(['En_cours', 'En attente', 'Terminee']),
            'conclusion' => $this->faker->boolean ? $this->faker->sentence(12) : null,
            'conforme' => $this->faker->boolean(70),
        ];
    }
}
