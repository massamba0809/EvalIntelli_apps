<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Question;
use App\Models\Domain;
use App\Http\Controllers\IaComparisonController;

class DebugEvaluationCommand extends Command
{
    protected $signature = 'debug:evaluation {question_id?}';
    protected $description = 'Debug l\'évaluation automatique des réponses IA';

    public function handle()
    {
        $questionId = $this->argument('question_id');

        if ($questionId) {
            $this->debugSpecificQuestion($questionId);
        } else {
            $this->debugLastProgrammingQuestion();
        }
    }

    private function debugSpecificQuestion($questionId)
    {
        $question = Question::with(['domain', 'iaResponses', 'evaluation'])->find($questionId);

        if (!$question) {
            $this->error("Question avec ID {$questionId} non trouvée");
            return;
        }

        $this->info("=== DEBUG QUESTION ID: {$questionId} ===");
        $this->debugQuestion($question);
    }

    private function debugLastProgrammingQuestion()
    {
        // Trouver les domaines de programmation
        $programmingDomains = Domain::where(function($query) {
            $query->where('name', 'LIKE', '%programmation%')
                ->orWhere('name', 'LIKE', '%code%')
                ->orWhere('name', 'LIKE', '%développement%')
                ->orWhere('slug', 'LIKE', '%programming%')
                ->orWhere('slug', 'LIKE', '%coding%');
        })->pluck('id');

        $question = Question::with(['domain', 'iaResponses', 'evaluation'])
            ->whereIn('domain_id', $programmingDomains)
            ->latest()
            ->first();

        if (!$question) {
            $this->error("Aucune question de programmation trouvée");
            return;
        }

        $this->info("=== DEBUG DERNIÈRE QUESTION DE PROGRAMMATION ===");
        $this->debugQuestion($question);
    }

    private function debugQuestion(Question $question)
    {
        $this->info("Question: " . substr($question->content, 0, 100) . "...");
        $this->info("Domaine: {$question->domain->name}");
        $this->info("Créée le: {$question->created_at}");

        // Vérifier si c'est une question de programmation
        $controller = new IaComparisonController(app(\App\Services\OpenRouterService::class));
        $isProgramming = $this->callProtectedMethod($controller, 'isProgrammingDomain', [$question->domain]);

        $this->info("Est une question de programmation: " . ($isProgramming ? 'OUI' : 'NON'));

        // Vérifier les réponses IA
        $this->info("\n=== RÉPONSES IA ===");
        $responses = $question->iaResponses;
        $this->info("Nombre de réponses: " . $responses->count());

        foreach ($responses as $response) {
            $this->info("- {$response->model_name}: " . ($response->response ? 'OK' : 'VIDE'));
            if (str_contains($response->response, 'Erreur:')) {
                $this->error("  ERREUR: " . substr($response->response, 0, 100));
            }
        }

        // Tester la méthode getResponsesByModel
        if (method_exists($question, 'getResponsesByModel')) {
            $responsesByModel = $question->getResponsesByModel();
            $this->info("\nRéponses groupées par modèle:");
            foreach ($responsesByModel as $model => $response) {
                $this->info("- {$model}: " . ($response ? 'OK' : 'NULL'));
            }
        } else {
            $this->error("Méthode getResponsesByModel manquante dans le modèle Question");
        }

        // Vérifier l'évaluation
        $this->info("\n=== ÉVALUATION ===");
        if ($question->evaluation) {
            $eval = $question->evaluation;
            $this->info("Évaluation existante:");
            $this->info("- GPT-4: {$eval->note_gpt4}");
            $this->info("- DeepSeek: {$eval->note_deepseek}");
            $this->info("- Qwen: {$eval->note_qwen}");
            $this->info("- Meilleure IA: {$eval->meilleure_ia}");
        } else {
            $this->error("Aucune évaluation trouvée");

            // Tenter de lancer l'évaluation manuellement
            if ($isProgramming && $responses->count() >= 3) {
                $this->info("\nTentative d'évaluation manuelle...");

                try {
                    $result = $controller->evaluateProgrammingResponses($question);

                    if ($result instanceof \Illuminate\Http\JsonResponse) {
                        $data = $result->getData(true);
                        if ($data['success']) {
                            $this->info("✅ Évaluation réussie! ID: " . $data['evaluation_id']);
                        } else {
                            $this->error("❌ Évaluation échouée: " . $data['message']);
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("❌ Exception lors de l'évaluation: " . $e->getMessage());
                    $this->error("Trace: " . $e->getTraceAsString());
                }
            }
        }

        // Tester la connexion à OpenRouter
        $this->info("\n=== TEST CONNEXION OPENROUTER ===");
        $openRouter = app(\App\Services\OpenRouterService::class);
        $connectionTest = $openRouter->testConnection();
        $this->info("Statut: " . $connectionTest['status']);
        $this->info("Message: " . $connectionTest['message']);

        // Tester l'appel à Claude directement
        if ($connectionTest['status'] === 'success') {
            $this->info("\n=== TEST APPEL CLAUDE ===");
            try {
                $testResult = $openRouter->queryModel('anthropic/claude-3.5-sonnet', 'Bonjour Claude, réponds juste "Test OK"');
                if ($testResult['status'] === 'success') {
                    $content = $testResult['response']['choices'][0]['message']['content'] ?? 'Pas de contenu';
                    $this->info("✅ Claude répond: " . $content);
                } else {
                    $this->error("❌ Erreur Claude: " . $testResult['response']);
                }
            } catch (\Exception $e) {
                $this->error("❌ Exception Claude: " . $e->getMessage());
            }
        }
    }

    /**
     * Appelle une méthode protégée d'une classe
     */
    private function callProtectedMethod($object, $method, $args = [])
    {
        $reflection = new \ReflectionClass($object);
        $reflectionMethod = $reflection->getMethod($method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invokeArgs($object, $args);
    }
}

// Pour utiliser cette commande, ajoutez-la dans app/Console/Kernel.php:
/*
protected $commands = [
    \App\Console\Commands\DebugEvaluationCommand::class,
];
*/

// Puis lancez:
// php artisan debug:evaluation
// ou
// php artisan debug:evaluation 123 (pour une question spécifique)
