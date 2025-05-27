<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Affectation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaiementFactory extends Factory
{
    public function definition(): array
    {
        $dateEcheance = $this->faker->dateTimeBetween('-1 month', '+2 months');
        $datePaiement = $this->faker->optional()->dateTimeBetween('-1 month', 'now');
        $statut = $this->faker->randomElement(['En attente', 'Validé', 'Annulé', 'En retard']);

        return [
            'id_utilisateur' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'id_affectation' => Affectation::inRandomOrder()->first()?->id ?? Affectation::factory(),
            'montant' => $this->faker->randomFloat(2, 10000, 500000),
            'datePaiement' => $datePaiement,
            'dateEcheance' => $dateEcheance,
            'methode_paiement' => $this->faker->optional()->randomElement(['Wave', 'Orange Money', 'Espèces', 'Autre']),
            'reference' => $this->faker->optional()->uuid,
            'statut' => $statut,
            'enregistre_par' => $this->faker->name,
        ];
    }
}
