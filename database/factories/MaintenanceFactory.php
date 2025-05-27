<?php

namespace Database\Factories;

use App\Models\Equipement;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaintenanceFactory extends Factory
{
    public function definition(): array
    {
        $dateSignalement = $this->faker->dateTimeBetween('-6 months', 'now');
        $dateDebut = $this->faker->optional()->dateTimeBetween($dateSignalement, '+1 week');
        $dateFin = $dateDebut ? $this->faker->optional()->dateTimeBetween($dateDebut, '+1 week') : null;

        return [
            'id_equipement' => Equipement::inRandomOrder()->first()?->id ?? Equipement::factory(),
            'type' => $this->faker->randomElement(['Préventive', 'Corrective', 'Urgente']),
            'description' => $this->faker->paragraph(),
            'priorite' => $this->faker->randomElement(['Faible', 'Normale', 'Élevée', 'Urgente']),
            'dateSignalement' => $dateSignalement,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'statut' => $this->faker->randomElement(['Signalée', 'Programmée', 'En cours', 'Terminée', 'Annulée']),
            'cout' => $this->faker->optional()->randomFloat(2, 5000, 500000),
            'remarques' => $this->faker->optional()->sentence(),
        ];
    }
}
