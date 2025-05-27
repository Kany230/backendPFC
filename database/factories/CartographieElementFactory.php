<?php

namespace Database\Factories;

use App\Models\CartographieElement;
use App\Models\Batiment;
use App\Models\Local;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartographieElementFactory extends Factory
{
    protected $model = CartographieElement::class;

    public function definition(): array
    {
        $types = ['Local', 'Couloir', 'Escalier', 'Ascenseur', 'Sortie', 'Autre'];

        return [
            'id_batiment' => Batiment::inRandomOrder()->first()?->id ?? Batiment::factory(),
            'id_local' => $this->faker->boolean(70) ? Local::inRandomOrder()->first()?->id ?? Local::factory() : null,
            'type' => $this->faker->randomElement($types),
            'coordonnees_x' => $this->faker->randomFloat(2, 0, 1000),
            'coordonnees_y' => $this->faker->randomFloat(2, 0, 1000),
            'largeur' => $this->faker->randomFloat(2, 10, 200),
            'hauteur' => $this->faker->randomFloat(2, 10, 200),
            'rotation' => $this->faker->randomFloat(2, 0, 360),
            'couleur' => $this->faker->hexColor(),
            'label' => $this->faker->optional()->word(),
            'details' => $this->faker->optional()->randomElement([
                json_encode(['info' => $this->faker->sentence()]),
                json_encode(['description' => $this->faker->paragraph()]),
                null
            ]),
        ];
    }
}
