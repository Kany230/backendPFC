<?php

namespace Database\Factories;

use App\Models\Alerte;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlerteFactory extends Factory
{
    protected $model = Alerte::class;

    public function definition(): array
    {
        $types = ['Maintenance', 'Contrat', 'Paiement', 'Système'];
        $priorites = ['Faible', 'Moyenne', 'Élevée', 'Critique'];

        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'type' => $this->faker->randomElement($types),
            'id_source' => $this->faker->optional()->numberBetween(1, 100), // id d'une source liée, ou null
            'message' => $this->faker->sentence(10),
            'dateCreation' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'dateEcheance' => $this->faker->optional()->dateTimeBetween('now', '+1 month'),
            'priorite' => $this->faker->randomElement($priorites),
            'vue' => $this->faker->boolean(20), // 20% de chances que ce soit true
        ];
    }
}
