<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Les commandes Artisan fournies par votre application.
     */
    protected $commands = [
        \App\Console\Commands\DebugEvaluationCommand::class,
        \App\Console\Commands\FixEvaluationDataCommand::class,
        \App\Console\Commands\TestWolframCommand::class,
    ];

    /**
     * Définir le programme de planification des commandes de l'application.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();

        // Optionnel : Vous pouvez programmer l'évaluation automatique des questions en attente
        // $schedule->call(function () {
        //     $questions = \App\Models\Question::needsEvaluation()->get();
        //     foreach ($questions as $question) {
        //         $controller = new \App\Http\Controllers\IaComparisonController(
        //             app(\App\Services\OpenRouterService::class)
        //         );
        //         $controller->triggerEvaluationIfProgramming($question);
        //     }
        // })->everyFiveMinutes();
    }

    /**
     * Enregistrer les commandes pour l'application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
