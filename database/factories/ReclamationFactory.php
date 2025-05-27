<?php

namespace Database\Factories;

use App\Models\Reclamation;
use App\Models\User;
use App\Models\Local;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReclamationFactory extends Factory
{
    protected $model = Reclamation::class;

    public function definition(): array
    {
        $statuts = ['Ouverte', 'Assignée', 'En cours', 'Résolue', 'Fermée'];
        $priorites = ['Faible', 'Normale', 'Élevée', 'Urgente'];

        $statut = $this->faker->randomElement($statuts);

        // dateResolution uniquement si statut est "Résolue" ou "Fermée"
        $dateResolution = in_array($statut, ['Résolue', 'Fermée']) ? $this->faker->dateTimeBetween('-1 month', 'now') : null;

        return [
            'id_utilisateur' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'id_local' => Local::inRandomOrder()->first()?->id ?? Local::factory(),
            'id_agent' => $this->faker->optional()->randomElement(User::pluck('id')->toArray()), // optionnel, peut être null
            'objet' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(2),
            'dateCreation' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'statut' => $statut,
            'priorite' => $this->faker->randomElement($priorites),
            'dateResolution' => $dateResolution,
            'solution' => $dateResolution ? $this->faker->paragraph() : null,
            'satisfaction' => $dateResolution ? $this->faker->numberBetween(1, 5) : null,
        ];
    }
}
