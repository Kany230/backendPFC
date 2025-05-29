<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Paiement;
use App\Services\EmailService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class EnvoyerRappelsPaiement extends Command
{
    protected $signature = 'rappels:paiements {--type=tous} {--force}';
    protected $description = 'Envoyer les rappels de paiement aux utilisateurs';

    private $emailService;

    public function __construct(EmailService $emailService)
    {
        parent::__construct();
        $this->emailService = $emailService;
    }

    public function handle()
    {
        $type = $this->option('type');
        $force = $this->option('force');

        if ($type === 'tous' || $type === 'retard') {
            $this->envoyerRappelsRetard();
        }

        if ($type === 'tous' || $type === 'mensuel') {
            // Vérifier si c'est le premier du mois, sauf si --force est utilisé
            if ($force || Carbon::now()->day === 1) {
                $this->envoyerRappelsMensuels();
            }
        }

        $this->info('Rappels envoyés avec succès !');
    }

    private function envoyerRappelsRetard()
    {
        $paiementsEnRetard = Paiement::where('dateEcheance', '<', now())
            ->where('statut', 'En attente')
            ->with('utilisateur')
            ->get()
            ->groupBy('id_utilisateur');

        foreach ($paiementsEnRetard as $userId => $paiements) {
            $user = User::find($userId);
            $this->emailService->envoyerRappelMensuel($user, $paiements->toArray());
            
            $this->info("Rappel de retard envoyé à {$user->email}");
        }
    }

    private function envoyerRappelsMensuels()
    {
        $utilisateurs = User::whereHas('affectations', function ($query) {
            $query->where('statut', 'Validé');
        })->get();

        foreach ($utilisateurs as $user) {
            $montantMensuel = $user->affectations()
                ->where('statut', 'Validé')
                ->sum('loyer_mensuel');

            if ($montantMensuel > 0) {
                $this->emailService->envoyerRappelDebutMois($user, $montantMensuel);
                $this->info("Rappel mensuel envoyé à {$user->email}");
            }
        }
    }
} 