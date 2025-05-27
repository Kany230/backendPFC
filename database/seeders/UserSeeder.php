<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Crée 10 utilisateurs aléatoires
        User::factory()->count(10)->create();

        // Crée un utilisateur admin spécifique
        User::factory()->create([
            'nom' => 'Admin',
            'prenom' => 'Principal',
            'email' => 'admin@example.com',
            'password' => bcrypt('admin123'), // mot de passe admin
            'statut' => 'Actif',
            'role' => 'admin',
            'sexe' => 'H',
            'telephone' => '0000000000',
            'adresse' => 'Siège Social',
        ]);
    }
}
