<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    protected function validator(array $data)
    {
        $rules = [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'telephone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', 'in:etudiant,commercant'],
        ];

        // Règles spécifiques pour les commerçants
        if ($data['role'] === 'commercant') {
            $rules['rccm'] = ['required', 'string', 'max:50', 'unique:users'];
            $rules['numero_contribuable'] = ['required', 'string', 'max:50', 'unique:users'];
            $rules['type_activite'] = ['required', 'string', 'max:100'];
        }

        // Règles spécifiques pour les étudiants
        if ($data['role'] === 'etudiant') {
            $rules['matricule'] = ['required', 'string', 'max:50', 'unique:users'];
            $rules['niveau'] = ['required', 'string', 'max:50'];
            $rules['filiere'] = ['required', 'string', 'max:100'];
        }

        return Validator::make($data, $rules);
    }

    protected function create(array $data)
    {
        $userData = [
            'nom' => $data['nom'],
            'prenom' => $data['prenom'],
            'email' => $data['email'],
            'telephone' => $data['telephone'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'statut' => 'actif'
        ];

        // Données spécifiques pour les commerçants
        if ($data['role'] === 'commercant') {
            $userData['rccm'] = $data['rccm'];
            $userData['numero_contribuable'] = $data['numero_contribuable'];
            $userData['type_activite'] = $data['type_activite'];
        }

        // Données spécifiques pour les étudiants
        if ($data['role'] === 'etudiant') {
            $userData['matricule'] = $data['matricule'];
            $userData['niveau'] = $data['niveau'];
            $userData['filiere'] = $data['filiere'];
        }

        return User::create($userData);
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        // Redirection en fonction du rôle
        $redirectTo = $user->role === 'etudiant' 
            ? route('etudiant.dashboard') 
            : route('commercant.dashboard');

        return redirect($redirectTo)
            ->with('success', 'Votre compte a été créé avec succès.');
    }
} 