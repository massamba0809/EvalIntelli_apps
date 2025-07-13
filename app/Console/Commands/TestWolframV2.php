<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WolframAlphaService;

/**
 * Commande pour tester Wolfram Alpha v2
 *
 * Créer le fichier : app/Console/Commands/TestWolframV2.php
 *
 * Usage:
 * php artisan wolfram:test-v2
 * php artisan wolfram:test-v2 "sqrt(16)"
 * php artisan wolfram:test-v2 "calculer 2 plus 2" --debug
 */
class TestWolframV2 extends Command
{
    /**
     * Signature de la commande
     */
    protected $signature = 'wolfram:test-v2
                            {question? : Question à tester (défaut: 2+2)}
                            {--debug : Mode debug complet}
                            {--french : Tester avec questions françaises}
                            {--batch : Tester plusieurs questions}';

    /**
     * Description de la commande
     */
    protected $description = 'Test le nouveau service Wolfram Alpha v2';

    /**
     * Exécution de la commande
     */
    public function handle()
    {
        $this->info('🧮 Test Wolfram Alpha v2');
        $this->line('============================');

        try {
            $wolfram = app(WolframAlphaService::class);

            // 1. Validation de la configuration
            $this->info('1. 🔧 Validation de la configuration...');
            $config = $wolfram->validateConfiguration();

            if (!$config['valid']) {
                $this->error('❌ Configuration invalide:');
                foreach ($config['issues'] as $issue) {
                    $this->error("   - {$issue}");
                }
                $this->line('');
                $this->info('💡 Solution:');
                $this->info('   - Ajoutez WOLFRAM_APP_ID=votre_clé dans .env');
                $this->info('   - Obtenez une clé sur https://developer.wolframalpha.com');
                $this->info('   - Redémarrez votre serveur Laravel');
                return 1;
            }

            $this->info('✅ Configuration valide');
            $this->info("   - Clé API: {$config['app_id_preview']}");
            $this->line('');

            // 2. Test de connexion
            $this->info('2. 🌐 Test de connexion...');
            $connectionTest = $wolfram->testConnection();

            if ($connectionTest['status'] === 'success') {
                $this->info('✅ Connexion réussie');
            } else {
                $this->error("❌ Connexion échouée: {$connectionTest['message']}");
                $this->line('');
                $this->info('💡 Vérifiez:');
                $this->info('   - Votre connexion internet');
                $this->info('   - L\'accès à api.wolframalpha.com');
                $this->info('   - La validité de votre clé API');
                return 1;
            }
            $this->line('');

            // 3. Mode batch
            if ($this->option('batch')) {
                return $this->runBatchTests($wolfram);
            }

            // 4. Mode français
            if ($this->option('french')) {
                return $this->runFrenchTests($wolfram);
            }

            // 5. Test de question simple ou fournie
            $question = $this->argument('question') ?? '2+2';
            $debug = $this->option('debug');

            $this->info("3. 🎯 Test de la question: {$question}");
            $this->line('');

            if ($debug) {
                return $this->runDebugTest($wolfram, $question);
            } else {
                return $this->runSimpleTest($wolfram, $question);
            }

        } catch (\Exception $e) {
            $this->error("💥 Exception: {$e->getMessage()}");

            if ($this->option('debug')) {
                $this->error('Trace complète:');
                $this->error($e->getTraceAsString());
            }

            return 1;
        }
    }

    /**
     * Test simple d'une question
     */
    protected function runSimpleTest(WolframAlphaService $wolfram, string $question): int
    {
        $result = $wolfram->querySimple($question);

        $this->info("📊 Résultats:");
        $this->info("   Status: {$result['status']}");
        $this->info("   Has reference: " . ($result['has_reference'] ? 'OUI ✅' : 'NON ❌'));
        $this->info("   Response time: {$result['response_time']}s");

        if ($result['status'] === 'success') {
            $this->info("   API utilisée: {$result['api_used']}");
            $this->info("   Variante utilisée: {$result['variant_used']}");
            $this->line('');
            $this->info("📝 Réponse:");
            $response = $result['response'];
            if (strlen($response) > 200) {
                $this->info('   ' . substr($response, 0, 200) . '...');
                $this->comment('   (Réponse tronquée - utilisez --debug pour voir la réponse complète)');
            } else {
                $this->info('   ' . $response);
            }

            $this->line('');
            $this->info('🎉 Test réussi!');
            return 0;

        } else {
            $this->error("❌ Erreur: {$result['response']}");
            $this->line('');
            $this->info('💡 Suggestions:');
            $this->info('   - Essayez une question plus simple (ex: "2+2")');
            $this->info('   - Utilisez --debug pour plus de détails');
            $this->info('   - Vérifiez que la question est mathématique');
            return 1;
        }
    }

    /**
     * Test avec debug complet
     */
    protected function runDebugTest(WolframAlphaService $wolfram, string $question): int
    {
        $result = $wolfram->debugQuestion($question);

        $this->info("🔍 Debug complet pour: {$question}");
        $this->line('');

        $this->info("📋 Informations générales:");
        $this->info("   Est mathématique: " . ($result['is_mathematical'] ? 'OUI ✅' : 'NON ❌'));
        $this->line('');

        $this->info("📝 Variantes générées:");
        foreach ($result['variants'] as $name => $variant) {
            $this->info("   - {$name}: {$variant}");
        }
        $this->line('');

        $this->info("🎯 Résultats par API:");
        $hasSuccess = false;
        foreach ($result['api_results'] as $api => $apiResult) {
            $status = $apiResult['success'] ? '✅ SUCCÈS' : '❌ ÉCHEC';
            $error = $apiResult['success'] ? '' : ' - ' . $apiResult['error'];
            $this->info("   - {$api}: {$status}{$error}");

            if ($apiResult['success']) {
                $hasSuccess = true;
                if (isset($apiResult['response'])) {
                    $response = substr($apiResult['response'], 0, 100);
                    $this->comment("     Réponse: {$response}...");
                }
            }
        }

        $this->line('');

        if ($hasSuccess) {
            $this->info('🎉 Au moins une stratégie a réussi!');
            return 0;
        } else {
            $this->error('❌ Toutes les stratégies ont échoué');
            return 1;
        }
    }

    /**
     * Tests de questions françaises
     */
    protected function runFrenchTests(WolframAlphaService $wolfram): int
    {
        $this->info('3. 🇫🇷 Tests de questions françaises...');
        $this->line('');

        $frenchQuestions = [
            'calculer 2 plus 2',
            'racine carrée de 16',
            'combien fait 5 fois 3',
            'résoudre x carré égal 9',
            'sinus de pi sur 2',
            'dérivée de x au carré'
        ];

        $successes = 0;
        $total = count($frenchQuestions);

        foreach ($frenchQuestions as $question) {
            $this->info("   Test: {$question}");

            $result = $wolfram->querySimple($question);
            $success = $result['status'] === 'success' && $result['has_reference'];

            if ($success) {
                $this->info("   ✅ Succès - API: {$result['api_used']}, Variante: {$result['variant_used']}");
                $this->comment("   Réponse: " . substr($result['response'], 0, 80) . "...");
                $successes++;
            } else {
                $this->error("   ❌ Échec - {$result['response']}");
            }

            $this->line('');
        }

        $rate = round(($successes / $total) * 100, 1);

        $this->info("📊 Résultat final:");
        $this->info("   Réussites: {$successes}/{$total} ({$rate}%)");

        if ($rate >= 80) {
            $this->info('🎉 Excellent taux de réussite pour le français!');
            return 0;
        } elseif ($rate >= 60) {
            $this->comment('⚠️ Taux de réussite moyen - certaines questions complexes échouent');
            return 0;
        } else {
            $this->error('❌ Taux de réussite faible - vérifiez la configuration');
            return 1;
        }
    }

    /**
     * Tests en lot de questions variées
     */
    protected function runBatchTests(WolframAlphaService $wolfram): int
    {
        $this->info('3. 📦 Tests en lot...');
        $this->line('');

        $testSets = [
            'Simples' => [
                '2+2', '5*3', '10-4', 'sqrt(9)', '3^2'
            ],
            'Moyens' => [
                'sqrt(36)', 'sin(pi/2)', 'cos(0)', 'log(10)'
            ],
            'Complexes' => [
                'derivative of x^2', 'solve x^2=4', 'integral of x'
            ],
            'Français' => [
                'calculer 2 plus 2', 'racine carrée de 16', 'sinus de 0'
            ]
        ];

        $globalSuccesses = 0;
        $globalTotal = 0;

        foreach ($testSets as $setName => $questions) {
            $this->info("🔸 Niveau {$setName}:");

            $setSuccesses = 0;
            foreach ($questions as $question) {
                $result = $wolfram->querySimple($question);
                $success = $result['status'] === 'success' && $result['has_reference'];

                $status = $success ? '✅' : '❌';
                $this->info("   {$status} {$question}");

                if ($success) {
                    $setSuccesses++;
                    $globalSuccesses++;
                }
                $globalTotal++;
            }

            $setRate = round(($setSuccesses / count($questions)) * 100, 1);
            $this->info("   📊 {$setSuccesses}/" . count($questions) . " ({$setRate}%)");
            $this->line('');
        }

        $globalRate = round(($globalSuccesses / $globalTotal) * 100, 1);

        $this->info("🏆 Résultat global:");
        $this->info("   Total: {$globalSuccesses}/{$globalTotal} ({$globalRate}%)");

        if ($globalRate >= 85) {
            $this->info('🎉 Excellent! Wolfram Alpha v2 fonctionne parfaitement!');
            return 0;
        } elseif ($globalRate >= 70) {
            $this->comment('⚠️ Bon taux de réussite - quelques améliorations possibles');
            return 0;
        } else {
            $this->error('❌ Taux de réussite insuffisant - vérifiez la configuration');
            return 1;
        }
    }
}
