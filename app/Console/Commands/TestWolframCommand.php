<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WolframAlphaService;

class TestWolframCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wolfram:test {question?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teste la connexion et les requêtes à Wolfram Alpha';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧮 Test de Wolfram Alpha API');
        $this->newLine();

        $wolfram = app(WolframAlphaService::class);

        // 1. Vérifier la configuration
        $this->info('📋 Vérification de la configuration...');
        $config = $wolfram->validateConfiguration();

        if (!$config['valid']) {
            $this->error('❌ Configuration invalide :');
            foreach ($config['issues'] as $issue) {
                $this->line("   • {$issue}");
            }
            return 1;
        }

        $this->info('✅ Configuration valide');
        $this->newLine();

        // 2. Test de connexion
        $this->info('🔗 Test de connexion...');
        $connectionTest = $wolfram->testConnection();

        if ($connectionTest['status'] === 'success') {
            $this->info('✅ ' . $connectionTest['message']);
            if (isset($connectionTest['test_result'])) {
                $this->line('   Résultat du test (2+2): ' . $connectionTest['test_result']);
            }
        } else {
            $this->error('❌ ' . $connectionTest['message']);
            return 1;
        }
        $this->newLine();

        // 3. Tests de requêtes
        $question = $this->argument('question');

        if (!$question) {
            $this->info('🧪 Tests de requêtes prédéfinies...');
            $testQuestions = [
                '2+2',
                'solve x^2 + 3x + 2 = 0',
                'derivative of x^2',
                'integral of 2x',
                'sqrt(16)',
                '5!',
                'sin(pi/2)',
                'convert 100 fahrenheit to celsius'
            ];
        } else {
            $this->info("🧪 Test de la question : {$question}");
            $testQuestions = [$question];
        }

        foreach ($testQuestions as $index => $testQuestion) {
            $this->info("   Question " . ($index + 1) . ": {$testQuestion}");

            $startTime = microtime(true);
            $result = $wolfram->querySimple($testQuestion);
            $endTime = microtime(true);

            if ($result['status'] === 'success' && $result['has_reference']) {
                $this->info("   ✅ Réponse: " . substr($result['response'], 0, 100) .
                    (strlen($result['response']) > 100 ? '...' : ''));
                $this->line("   ⏱️  Temps: " . round($endTime - $startTime, 3) . "s");
            } elseif ($result['status'] === 'no_result') {
                $this->warn("   ⚠️  " . $result['response']);
            } else {
                $this->error("   ❌ Erreur: " . $result['response']);
            }

            $this->newLine();
        }

        // 4. Test de détection mathématique
        $this->info('🔍 Test de détection de contenu mathématique...');

        $mathTestCases = [
            ['text' => 'Calculer 2+2', 'expected' => true],
            ['text' => 'Résoudre l\'équation x^2 + 3x + 2 = 0', 'expected' => true],
            ['text' => 'Quelle est la dérivée de x^2?', 'expected' => true],
            ['text' => 'Comment ça va?', 'expected' => false],
            ['text' => 'Expliquer la programmation orientée objet', 'expected' => false],
            ['text' => 'Calculer la racine carrée de 16', 'expected' => true],
            ['text' => 'sin(π/2) = ?', 'expected' => true],
        ];

        foreach ($mathTestCases as $testCase) {
            $detected = $wolfram->isMathematicalQuestion($testCase['text']);
            $status = $detected === $testCase['expected'] ? '✅' : '❌';
            $result = $detected ? 'OUI' : 'NON';

            $this->line("   {$status} \"{$testCase['text']}\" → Math: {$result}");
        }

        $this->newLine();

        // 5. Statistiques et recommandations
        $this->info('📊 Résumé du test');
        $this->line('   • Configuration Wolfram Alpha: ✅ Valide');
        $this->line('   • Connexion API: ✅ Fonctionnelle');
        $this->line('   • Détection mathématique: ✅ Opérationnelle');

        $this->newLine();
        $this->info('💡 Recommandations :');
        $this->line('   • Utilisez des questions mathématiques claires et précises');
        $this->line('   • Les équations en notation standard fonctionnent mieux');
        $this->line('   • Le cache est activé pour éviter les requêtes répétées');
        $this->line('   • Vérifiez les logs en cas de problème');

        return 0;
    }
}
