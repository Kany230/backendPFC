<?php

namespace Database\Factories;

use App\Models\Affectation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ContratFactory extends Factory
{
    public function definition(): array
    {
        $dateDebut = $this->faker->dateTimeBetween('-1 year', 'now');
        $dateFin = (clone $dateDebut)->modify('+1 year');

        return [
            'id_affectation' => Affectation::inRandomOrder()->first()?->id ?? Affectation::factory(),
            'reference' => strtoupper(Str::random(10)) . '-' . $this->faker->unique()->numberBetween(1000, 9999),
            'dateDebut' => $dateDebut->format('Y-m-d'),
            'dateFin' => $dateFin->format('Y-m-d'),
            'montant' => $this->faker->randomFloat(2, 100000, 1000000),
            'frequence_paiement' => $this->faker->randomElement(['Mensuel', 'Trimestriel', 'Semestriel', 'Annuel']),
            'type' => $this->faker->randomElement(['Location', 'Sous-location', 'Convention']),
            'statut' => $this->faker->randomElement(['Actif', 'Expiré', 'Résilié']),
        ];
    }
}
