<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Vérifier les paiements en retard tous les jours à 8h
        $schedule->command('rappels:paiements --type=retard')
                ->dailyAt('08:00')
                ->timezone('Africa/Dakar');

        // Envoyer les rappels mensuels le 1er du mois à 8h
        $schedule->command('rappels:paiements --type=mensuel')
                ->monthlyOn(1, '08:00')
                ->timezone('Africa/Dakar');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
} 