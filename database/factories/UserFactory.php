<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition()
    {
        $roles = ['etudiant', 'commercant', 'admin', 'gestionnaire', 'technicien', 'agentQHSE', 'chefpavillon'];
        $statuts = ['Actif', 'Inactif', 'Suspendu'];
        $sexes = ['F', 'H'];

        return [
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'sexe' => $this->faker->randomElement($sexes),
            'telephone' => $this->faker->phoneNumber(),
            'adresse' => $this->faker->address(),
            'password' => Hash::make('password'),
            'statut' => $this->faker->randomElement($statuts),
            'role' => $this->faker->randomElement($roles),
            'photo' => null,
            'remember_token' => Str::random(10),
        ];
    }
}
