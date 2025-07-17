<?php

use App\Http\Controllers\IaComparisonController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ChimieController; // NOUVEAU
use App\Http\Controllers\SidebarApiController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {

    // === ROUTES PRINCIPALES ===
    Route::get('/dashboard', [IaComparisonController::class, 'index'])->name('dashboard');

    // Routes IA principales
    Route::prefix('ia')->group(function () {
        Route::get('/', [IaComparisonController::class, 'index'])->name('ia.index');
        Route::get('/{domain}', [QuestionController::class, 'showForm'])->name('ia.form');
        Route::post('/{domain}', [QuestionController::class, 'submit'])->name('ia.submit');

        // Route pour afficher les résultats d'une question spécifique
        Route::get('/results', [QuestionController::class, 'showResultsById'])->name('ia.results.by.id');
    });

    // === ROUTES ÉVALUATIONS (TOUS DOMAINES) ===
    Route::prefix('questions')->group(function () {
        // Routes d'évaluation principales - ROUTE UNIFIÉE
        Route::post('/{question}/evaluate', [EvaluationController::class, 'evaluate'])
            ->name('questions.evaluate')
            ->where('question', '[0-9]+');

        Route::get('/{question}/evaluation', [EvaluationController::class, 'show'])
            ->name('questions.evaluation.show')
            ->where('question', '[0-9]+');

        Route::get('/{question}/evaluation/status', [EvaluationController::class, 'getStatus'])
            ->name('questions.evaluation.status')
            ->where('question', '[0-9]+');

        // NOUVELLE ROUTE : Résumé rapide de l'évaluation
        Route::get('/{question}/evaluation/summary', [EvaluationController::class, 'getSummary'])
            ->name('questions.evaluation.summary')
            ->where('question', '[0-9]+');

        // NOUVELLE ROUTE : Relancer une évaluation
        Route::post('/{question}/evaluation/retry', [EvaluationController::class, 'retryEvaluation'])
            ->name('questions.evaluation.retry')
            ->where('question', '[0-9]+');

        // ANCIENNE ROUTE : Compatibilité
        Route::post('/{question}/reprocess', [EvaluationController::class, 'retryEvaluation'])
            ->name('questions.reprocess')
            ->where('question', '[0-9]+');
    });

    // === 🧪 ROUTES SPÉCIFIQUES CHIMIE ===
    Route::prefix('chimie')->name('chimie.')->group(function () {
        // Affichage de l'évaluation chimie
        Route::get('/evaluation/{question}', [ChimieController::class, 'show'])
            ->name('evaluation.show')
            ->where('question', '[0-9]+');

        // API d'évaluation chimie
        Route::post('/evaluate/{question}', [ChimieController::class, 'evaluateChemistryQuestion'])
            ->name('evaluate')
            ->where('question', '[0-9]+');

        // Statut d'évaluation chimie
        Route::get('/status/{question}', [ChimieController::class, 'getChemistryEvaluationStatus'])
            ->name('status')
            ->where('question', '[0-9]+');
    });

    // === ROUTES STATISTIQUES ET DASHBOARD ===
    Route::prefix('evaluations')->group(function () {
        // Statistiques générales
        Route::get('/stats', [EvaluationController::class, 'getDashboardStats'])
            ->name('evaluations.stats');

        // Recherche et filtres
        Route::get('/search', [EvaluationController::class, 'searchEvaluations'])
            ->name('evaluations.search');

        // Comparaison IA par domaine
        Route::get('/compare/{domain}', [EvaluationController::class, 'compareAIs'])
            ->name('evaluations.compare')
            ->where('domain', '[a-z-]+');

        // Top évaluations
        Route::get('/top/{criteria?}', [EvaluationController::class, 'getTopEvaluations'])
            ->name('evaluations.top')
            ->where('criteria', '(score|wolfram_success|recent)');

        // Tendances
        Route::get('/trends/{days?}', [EvaluationController::class, 'getEvaluationTrends'])
            ->name('evaluations.trends')
            ->where('days', '[0-9]+');

        // Rapport d'analyse
        Route::post('/report', [EvaluationController::class, 'generateAnalysisReport'])
            ->name('evaluations.report');

        // Maintenance
        Route::post('/cleanup', [EvaluationController::class, 'performCleanup'])
            ->name('evaluations.cleanup');
    });

    // === ROUTES API SIDEBAR ===
    Route::prefix('api')->group(function () {
        // Gestion des questions utilisateur
        Route::get('/user/questions', [SidebarApiController::class, 'getUserQuestions'])
            ->name('api.user.questions');

        Route::get('/user/questions/{question}', [SidebarApiController::class, 'getQuestionDetails'])
            ->name('api.user.question.details')
            ->where('question', '[0-9]+');

        // Recherche
        Route::get('/user/search', [SidebarApiController::class, 'searchUserQuestions'])
            ->name('api.user.search');

        // Statistiques utilisateur
        Route::get('/user/stats', [SidebarApiController::class, 'getUserStats'])
            ->name('api.user.stats');

        // Questions récentes
        Route::get('/user/recent', [SidebarApiController::class, 'getRecentQuestions'])
            ->name('api.user.recent');

        // Suppression
        Route::delete('/user/questions/{question}', [SidebarApiController::class, 'deleteQuestion'])
            ->name('api.user.question.delete')
            ->where('question', '[0-9]+');

        Route::delete('/user/questions', [SidebarApiController::class, 'deleteBulkQuestions'])
            ->name('api.user.questions.bulk.delete');

        Route::delete('/user/history/clear', [SidebarApiController::class, 'clearAllHistory'])
            ->name('api.user.history.clear');

        // === 🆕 NOUVELLES ROUTES API ÉVALUATIONS ===
        Route::prefix('evaluations')->group(function () {
            // API pour les statistiques d'évaluation
            Route::get('/dashboard', [EvaluationController::class, 'getDashboardStats'])
                ->name('api.evaluations.dashboard');

            // API pour les statistiques par domaine
            Route::get('/domain/{domain}/stats', function($domain) {
                $evaluationService = app(\App\Services\EvaluationService::class);
                return response()->json($evaluationService->getDomainEvaluationStats($domain));
            })->name('api.evaluations.domain.stats')
                ->where('domain', '[a-z-]+');

            // API pour recherche d'évaluations
            Route::get('/search', [EvaluationController::class, 'searchEvaluations'])
                ->name('api.evaluations.search');

            // API pour évaluations récentes
            Route::get('/recent/{limit?}', function($limit = 10) {
                $evaluationService = app(\App\Services\EvaluationService::class);
                return response()->json($evaluationService->getTopEvaluations('recent', $limit));
            })->name('api.evaluations.recent')
                ->where('limit', '[0-9]+');

            // API pour le statut global Wolfram Alpha
            Route::get('/wolfram/status', function() {
                $stats = \App\Models\Evaluation::whereIn('wolfram_status', ['success', 'failed'])
                    ->selectRaw('
                        wolfram_status,
                        COUNT(*) as count,
                        AVG(CASE WHEN wolfram_status = "success" THEN 1 ELSE 0 END) * 100 as success_rate
                    ')
                    ->groupBy('wolfram_status')
                    ->get();

                return response()->json([
                    'success' => true,
                    'wolfram_stats' => $stats
                ]);
            })->name('api.evaluations.wolfram.status');
        });

        // === 🧪 ROUTES API CHIMIE ===
        Route::prefix('chimie')->group(function () {
            // API spécifique chimie
            Route::post('/evaluate/{question}', [ChimieController::class, 'evaluateChemistryQuestion'])
                ->name('api.chimie.evaluate')
                ->where('question', '[0-9]+');

            Route::get('/status/{question}', [ChimieController::class, 'getChemistryEvaluationStatus'])
                ->name('api.chimie.status')
                ->where('question', '[0-9]+');

            // Nouvelle route : Test de détection chimie
            Route::get('/detect/{question}', [ChimieController::class, 'testChemistryDetection'])
                ->name('api.chimie.detect')
                ->where('question', '[0-9]+');

            // Nouvelle route : Validation configuration
            Route::get('/config/validate', function() {
                $chimieService = app(\App\Services\ChimieEvaluationService::class);
                return response()->json($chimieService->validateConfiguration());
            })->name('api.chimie.config.validate');

            // Nouvelle route : Test pipeline complet (dev seulement)
            Route::post('/test/pipeline', function(Illuminate\Http\Request $request) {
                if (!app()->environment('local', 'development')) {
                    abort(404);
                }

                $question = $request->input('question', 'Quelle est la formule de l\'eau ?');
                $chimieService = app(\App\Services\ChimieEvaluationService::class);

                return response()->json($chimieService->testEvaluationPipeline($question));
            })->name('api.chimie.test.pipeline');
        });
    });

    // === ROUTES AJAX POUR L'INTERFACE ===
    Route::prefix('ajax')->group(function () {
        // Déclenchement d'évaluation via AJAX
        Route::post('/evaluate/{question}', [EvaluationController::class, 'evaluate'])
            ->name('ajax.evaluate')
            ->where('question', '[0-9]+');

        // Vérification de statut via AJAX
        Route::get('/status/{question}', [EvaluationController::class, 'getStatus'])
            ->name('ajax.status')
            ->where('question', '[0-9]+');

        // Récupération des résultats formatés pour l'affichage
        Route::get('/results/{question}', function($questionId) {
            $question = \App\Models\Question::with(['domain', 'iaResponses', 'evaluation'])
                ->where('user_id', auth()->id())
                ->findOrFail($questionId);

            return response()->json([
                'success' => true,
                'question' => $question,
                'evaluation' => $question->evaluation ? [
                    'type' => $question->evaluation->evaluation_type,
                    'best_ai' => $question->evaluation->meilleure_ia,
                    'scores' => [
                        'gpt4' => $question->evaluation->note_gpt4,
                        'deepseek' => $question->evaluation->note_deepseek,
                        'qwen' => $question->evaluation->note_qwen
                    ],
                    'has_wolfram' => $question->evaluation->hasWolframReference(),
                    'wolfram_status' => $question->evaluation->wolfram_status
                ] : null
            ]);
        })->name('ajax.results')
            ->where('question', '[0-9]+');
    });

    // === 🔧 ROUTES DE DEBUG (TEMPORAIRES) ===
    Route::prefix('debug')->group(function () {
        // Test de détection de domaine
        Route::get('/detect/{question}', function($questionId) {
            $question = \App\Models\Question::with('domain')->findOrFail($questionId);

            return response()->json([
                'question_id' => $question->id,
                'domain' => $question->domain->name,
                'detections' => [
                    'is_programming' => $question->isProgrammingQuestion(),
                    'is_mathematical' => $question->isMathematicalQuestion(),
                    'is_translation' => method_exists($question, 'isTranslationQuestion') ?
                        $question->isTranslationQuestion() : 'METHOD_NOT_FOUND',
                    'is_chemistry' => method_exists($question, 'isChemistryQuestion') ?
                        $question->isChemistryQuestion() : 'METHOD_NOT_FOUND',
                    'evaluation_type' => $question->getEvaluationType(),
                    'is_evaluable' => $question->isEvaluableQuestion()
                ]
            ]);
        })->name('debug.detect')
            ->where('question', '[0-9]+');

        // 🧪 DIAGNOSTIC COMPLET CHIMIE
        Route::get('/evaluation-error/{question}', function($questionId) {
            try {
                $question = \App\Models\Question::with(['domain', 'iaResponses', 'evaluation'])
                    ->where('user_id', auth()->id())
                    ->findOrFail($questionId);

                $debug = [
                    'step_1_question_info' => [
                        'id' => $question->id,
                        'content' => $question->content,
                        'domain' => $question->domain->name ?? 'N/A',
                        'responses_count' => $question->iaResponses->count(),
                        'has_evaluation' => $question->evaluation ? true : false
                    ],

                    'step_2_detection' => [
                        'has_isChemistryQuestion_method' => method_exists($question, 'isChemistryQuestion'),
                        'isChemistryQuestion' => method_exists($question, 'isChemistryQuestion') ?
                            $question->isChemistryQuestion() : 'METHOD_NOT_FOUND',
                        'isEvaluableQuestion' => $question->isEvaluableQuestion(),
                        'getEvaluationType' => $question->getEvaluationType(),
                        'hasAllAIResponses' => method_exists($question, 'hasAllAIResponses') ?
                            $question->hasAllAIResponses() : 'METHOD_NOT_FOUND'
                    ],

                    'step_3_services_test' => [],
                    'step_4_evaluation_test' => [],
                    'step_5_errors' => []
                ];

                // Test 1: Vérifier les services
                try {
                    $debug['step_3_services_test']['ChimieEvaluationService_exists'] = class_exists('\App\Services\ChimieEvaluationService');

                    if (class_exists('\App\Services\ChimieEvaluationService')) {
                        $chimieService = app(\App\Services\ChimieEvaluationService::class);
                        $debug['step_3_services_test']['service_instantiated'] = true;
                        $debug['step_3_services_test']['isChemistryQuestion'] = $chimieService->isChemistryQuestion($question);

                        // Test validation config
                        $debug['step_3_services_test']['config_validation'] = $chimieService->validateConfiguration();
                    }
                } catch (\Exception $e) {
                    $debug['step_3_services_test']['error'] = $e->getMessage();
                    $debug['step_3_services_test']['trace'] = $e->getTraceAsString();
                }

                // Test 2: Vérifier les réponses IA
                try {
                    if (method_exists($question, 'getResponsesByModel')) {
                        $responsesByModel = $question->getResponsesByModel();
                        $debug['step_4_evaluation_test']['responses_by_model'] = [
                            'gpt4_exists' => isset($responsesByModel['gpt4']),
                            'deepseek_exists' => isset($responsesByModel['deepseek']),
                            'qwen_exists' => isset($responsesByModel['qwen']),
                            'gpt4_length' => isset($responsesByModel['gpt4']) ? strlen($responsesByModel['gpt4']->response) : 0,
                            'deepseek_length' => isset($responsesByModel['deepseek']) ? strlen($responsesByModel['deepseek']->response) : 0,
                            'qwen_length' => isset($responsesByModel['qwen']) ? strlen($responsesByModel['qwen']->response) : 0,
                        ];
                    } else {
                        $debug['step_4_evaluation_test']['responses_error'] = 'Méthode getResponsesByModel() non trouvée';
                    }
                } catch (\Exception $e) {
                    $debug['step_4_evaluation_test']['responses_error'] = $e->getMessage();
                }

                return response()->json($debug, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            } catch (\Exception $e) {
                return response()->json([
                    'critical_error' => [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString()
                    ]
                ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

        })->name('debug.evaluation.error');

        // 🚀 FORCER UNE ÉVALUATION MANUELLEMENT
        Route::post('/force-evaluation/{question}', function($questionId) {
            try {
                $question = \App\Models\Question::with(['domain', 'iaResponses', 'evaluation'])
                    ->where('user_id', auth()->id())
                    ->findOrFail($questionId);

                if (!method_exists($question, 'isChemistryQuestion') || !$question->isChemistryQuestion()) {
                    return response()->json([
                        'error' => 'Question non détectée comme chimie',
                        'evaluation_type' => $question->getEvaluationType(),
                        'has_method' => method_exists($question, 'isChemistryQuestion')
                    ], 400);
                }

                if (!method_exists($question, 'hasAllAIResponses') || !$question->hasAllAIResponses()) {
                    return response()->json([
                        'error' => 'Réponses IA incomplètes',
                        'responses_count' => $question->iaResponses->count(),
                        'has_method' => method_exists($question, 'hasAllAIResponses')
                    ], 400);
                }

                // Tenter l'évaluation
                $chimieService = app(\App\Services\ChimieEvaluationService::class);
                $result = $chimieService->evaluateQuestion($question);

                return response()->json([
                    'success' => $result['success'],
                    'result' => $result
                ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => explode("\n", $e->getTraceAsString())
                ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }

        })->name('debug.force.evaluation');

        // Test des services
        Route::get('/test/wolfram/{query}', function($query) {
            $wolfram = app(\App\Services\WolframAlphaService::class);
            return response()->json($wolfram->querySimple($query));
        })->name('debug.wolfram');

        Route::get('/test/deepl/{text}', function($text) {
            $deepl = app(\App\Services\DeepLService::class);
            return response()->json($deepl->translate($text, 'EN', 'FR'));
        })->name('debug.deepl');

        // Test simple chimie
        Route::get('/test-chimie/{question}', function($questionId) {
            $question = \App\Models\Question::findOrFail($questionId);

            return response()->json([
                'question_id' => $question->id,
                'content' => $question->content,
                'simple_tests' => [
                    'contains_chimie' => str_contains(strtolower($question->content), 'chimie'),
                    'contains_chemistry' => str_contains(strtolower($question->content), 'chemistry'),
                    'contains_formula' => str_contains(strtolower($question->content), 'formule'),
                    'has_method_isChemistryQuestion' => method_exists($question, 'isChemistryQuestion'),
                    'ChimieEvaluationService_exists' => class_exists('\App\Services\ChimieEvaluationService')
                ]
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        })->name('debug.test.chimie');
    });
});

// === ROUTES PUBLIQUES (SI NÉCESSAIRE) ===
Route::prefix('public')->group(function () {
    // Statistiques publiques (anonymisées)
    Route::get('/stats', function() {
        $stats = [
            'total_evaluations' => \App\Models\Evaluation::count(),
            'domains_available' => \App\Models\Domain::count(),
            'average_scores' => \App\Models\Evaluation::selectRaw('
                AVG(note_gpt4) as avg_gpt4,
                AVG(note_deepseek) as avg_deepseek,
                AVG(note_qwen) as avg_qwen
            ')->first(),
            'wolfram_success_rate' => \App\Models\Evaluation::whereIn('wolfram_status', ['success', 'failed'])
                ->selectRaw('AVG(CASE WHEN wolfram_status = "success" THEN 1 ELSE 0 END) * 100 as success_rate')
                ->value('success_rate')
        ];

        return response()->json([
            'success' => true,
            'public_stats' => $stats
        ]);
    })->name('public.stats');

    // Informations sur les domaines disponibles
    Route::get('/domains', function() {
        $domains = \App\Models\Domain::select(['name', 'slug', 'icon', 'description'])->get();

        return response()->json([
            'success' => true,
            'domains' => $domains
        ]);
    })->name('public.domains');
});

// === GESTION D'ERREURS PERSONNALISÉES ===
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route non trouvée',
        'available_routes' => [
            'main' => '/ia',
            'evaluate' => '/questions/{id}/evaluate',
            'stats' => '/evaluations/stats',
            'api' => '/api/user/questions',
            'debug' => '/debug/evaluation-error/{id} (temporaire)'
        ]
    ], 404);
});














// Routes de debug pour le domaine Chimie (uniquement en développement)
Route::group(['middleware' => ['auth'], 'prefix' => 'debug/chimie'], function () {

    // Health check du service chimie
    Route::get('/health', [ChimieController::class, 'healthCheck'])
        ->name('debug.chimie.health');

    // Test de détection chimie pour une question
    Route::get('/detect/{question}', [ChimieController::class, 'testChemistryDetection'])
        ->name('debug.chimie.detect')
        ->where('question', '[0-9]+');

    // Force une réévaluation
    Route::post('/force-eval/{question}', [ChimieController::class, 'forceReEvaluation'])
        ->name('debug.chimie.force.eval')
        ->where('question', '[0-9]+');

    // Test complet d'évaluation avec logs détaillés
    Route::get('/test-eval/{question}', function($questionId) {
        if (!app()->environment('local', 'development')) {
            abort(404);
        }

        try {
            $question = \App\Models\Question::findOrFail($questionId);

            // Vérifications préliminaires
            $checks = [
                'question_exists' => true,
                'user_owns_question' => $question->user_id === Auth::id(),
                'has_domain' => $question->domain !== null,
                'domain_name' => $question->domain->name ?? 'N/A',
                'responses_count' => $question->iaResponses->count(),
                'has_all_responses' => method_exists($question, 'hasAllAIResponses') ? $question->hasAllAIResponses() : false,
                'existing_evaluation' => $question->evaluation !== null,
                'existing_evaluation_type' => $question->evaluation?->evaluation_type ?? 'none'
            ];

            // Test du service chimie
            $chimieService = app(\App\Services\ChimieEvaluationService::class);
            $serviceTests = [
                'service_instantiated' => $chimieService !== null,
                'is_chemistry_method' => method_exists($chimieService, 'isChemistryQuestion'),
                'evaluate_method' => method_exists($chimieService, 'evaluateQuestion')
            ];

            // Test de détection
            $detectionTest = [];
            try {
                $detectionTest['is_chemistry'] = $chimieService->isChemistryQuestion($question);
                $detectionTest['analyze_result'] = method_exists($chimieService, 'analyzeChemistryQuestion') ?
                    $chimieService->analyzeChemistryQuestion($question->content) : 'Method not found';
            } catch (\Exception $e) {
                $detectionTest['error'] = $e->getMessage();
                $detectionTest['is_chemistry'] = false;
            }

            // Test des dépendances
            $dependencies = [
                'openrouter_service' => app()->bound(\App\Services\OpenRouterService::class),
                'wolfram_service' => app()->bound(\App\Services\WolframAlphaService::class),
                'deepl_service' => app()->bound(\App\Services\DeepLService::class),
            ];

            return response()->json([
                'question_id' => $question->id,
                'content_preview' => substr($question->content, 0, 200) . '...',
                'preliminary_checks' => $checks,
                'service_tests' => $serviceTests,
                'detection_test' => $detectionTest,
                'dependencies' => $dependencies,
                'next_steps' => [
                    'can_proceed' => $checks['has_all_responses'] && $serviceTests['service_instantiated'],
                    'recommendation' => $checks['has_all_responses'] ?
                        'Prêt pour évaluation' :
                        'Attendre toutes les réponses IA'
                ]
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du test',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString())
            ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    })->name('debug.chimie.test.eval');

    // Simulate une évaluation chimie step by step
    Route::get('/simulate/{question}', function($questionId) {
        if (!app()->environment('local', 'development')) {
            abort(404);
        }

        try {
            $question = \App\Models\Question::findOrFail($questionId);
            $chimieService = app(\App\Services\ChimieEvaluationService::class);

            $steps = [];

            // Étape 1: Détection chimie
            $steps['step_1_detection'] = [
                'description' => 'Détection question chimie',
                'status' => 'running'
            ];

            try {
                $isChemistry = $chimieService->isChemistryQuestion($question);
                $steps['step_1_detection']['status'] = 'success';
                $steps['step_1_detection']['result'] = $isChemistry;
            } catch (\Exception $e) {
                $steps['step_1_detection']['status'] = 'error';
                $steps['step_1_detection']['error'] = $e->getMessage();

                return response()->json([
                    'simulation_stopped' => 'Étape 1 échouée',
                    'steps' => $steps
                ], 500);
            }

            // Étape 2: Vérification réponses IA
            $steps['step_2_responses'] = [
                'description' => 'Vérification réponses IA',
                'status' => 'running'
            ];

            if ($question->hasAllAIResponses()) {
                $steps['step_2_responses']['status'] = 'success';
                $steps['step_2_responses']['result'] = 'Toutes les réponses disponibles';
            } else {
                $steps['step_2_responses']['status'] = 'error';
                $steps['step_2_responses']['error'] = 'Réponses IA manquantes';

                return response()->json([
                    'simulation_stopped' => 'Étape 2 échouée',
                    'steps' => $steps
                ], 400);
            }

            // Étape 3: Test Wolfram (sans faire l'appel)
            $steps['step_3_wolfram'] = [
                'description' => 'Préparation Wolfram Alpha',
                'status' => 'simulated',
                'result' => 'Étape simulée - pas d\'appel réel'
            ];

            // Étape 4: Test Claude (sans faire l'appel)
            $steps['step_4_claude'] = [
                'description' => 'Préparation évaluation Claude',
                'status' => 'simulated',
                'result' => 'Étape simulée - pas d\'appel réel'
            ];

            // Étape 5: Test sauvegarde (sans sauvegarder)
            $steps['step_5_save'] = [
                'description' => 'Simulation sauvegarde',
                'status' => 'simulated',
                'result' => 'Étape simulée - pas de sauvegarde réelle'
            ];

            return response()->json([
                'simulation_success' => true,
                'question_id' => $question->id,
                'ready_for_evaluation' => true,
                'steps' => $steps,
                'note' => 'Simulation réussie - l\'évaluation réelle devrait fonctionner'
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            return response()->json([
                'simulation_error' => true,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    })->name('debug.chimie.simulate');

    // Test des logs d'évaluation chimie
    Route::get('/logs/{question?}', function($questionId = null) {
        if (!app()->environment('local', 'development')) {
            abort(404);
        }

        // Créer des logs de test pour voir si le système de logging fonctionne
        Log::info('🧪 TEST LOG CHIMIE', [
            'test_time' => now(),
            'question_id' => $questionId,
            'test_type' => 'debug_route'
        ]);

        $logTests = [
            'log_channels' => config('logging.channels'),
            'current_log_level' => config('logging.level', 'debug'),
            'test_log_written' => 'Check your logs for the test message above'
        ];

        if ($questionId) {
            try {
                $question = \App\Models\Question::findOrFail($questionId);
                Log::info('🧪 QUESTION SPECIFIC TEST', [
                    'question_id' => $question->id,
                    'content_length' => strlen($question->content),
                    'domain' => $question->domain->name ?? 'N/A',
                    'responses_count' => $question->iaResponses->count()
                ]);
                $logTests['question_specific_log'] = 'Logged for question ' . $questionId;
            } catch (\Exception $e) {
                $logTests['question_error'] = $e->getMessage();
            }
        }

        return response()->json([
            'log_tests' => $logTests,
            'recommendation' => 'Check your application logs (storage/logs/laravel.log) for the test messages'
        ], 200, [], JSON_PRETTY_PRINT);
    })->name('debug.chimie.logs');
});

// Route pour lister toutes les questions de chimie détectées
Route::get('/debug/all-chemistry-questions', function() {
    if (!app()->environment('local', 'development')) {
        abort(404);
    }

    try {
        $chimieService = app(\App\Services\ChimieEvaluationService::class);
        $allQuestions = \App\Models\Question::with(['domain', 'evaluation'])->get();

        $chemistryQuestions = [];
        $nonChemistryQuestions = [];

        foreach ($allQuestions as $question) {
            try {
                $isChemistry = $chimieService->isChemistryQuestion($question);

                $questionData = [
                    'id' => $question->id,
                    'content_preview' => substr($question->content, 0, 100) . '...',
                    'domain' => $question->domain->name ?? 'N/A',
                    'has_evaluation' => $question->evaluation !== null,
                    'evaluation_type' => $question->evaluation?->evaluation_type ?? 'none'
                ];

                if ($isChemistry) {
                    $chemistryQuestions[] = $questionData;
                } else {
                    $nonChemistryQuestions[] = $questionData;
                }

            } catch (\Exception $e) {
                $nonChemistryQuestions[] = [
                    'id' => $question->id,
                    'error' => 'Detection failed: ' . $e->getMessage()
                ];
            }
        }

        return response()->json([
            'total_questions' => count($allQuestions),
            'chemistry_questions' => [
                'count' => count($chemistryQuestions),
                'questions' => $chemistryQuestions
            ],
            'non_chemistry_questions' => [
                'count' => count($nonChemistryQuestions),
                'questions' => array_slice($nonChemistryQuestions, 0, 10) // Limiter l'affichage
            ]
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erreur lors de l\'analyse',
            'message' => $e->getMessage()
        ], 500);
    }
})->middleware('auth')->name('debug.all.chemistry.questions');


// === À AJOUTER dans routes/web.php ===

// Après les routes existantes, ajouter ces corrections et nouvelles routes :

Route::middleware(['auth', 'verified'])->group(function () {

    // === 🧪 ROUTES CHIMIE CORRIGÉES ET COMPLÈTES ===

    // 1. ROUTES API CHIMIE (dans le groupe /api)
    Route::prefix('api')->group(function () {
        Route::prefix('chimie')->group(function () {

            // Route principale d'évaluation chimie
            Route::post('/evaluate/{question}', [ChimieController::class, 'evaluateChemistryQuestion'])
                ->name('api.chimie.evaluate')
                ->where('question', '[0-9]+');

            // Statut de l'évaluation chimie
            Route::get('/status/{question}', [ChimieController::class, 'getChemistryEvaluationStatus'])
                ->name('api.chimie.status')
                ->where('question', '[0-9]+');

            // Test de détection chimie
            Route::get('/detect/{question}', [ChimieController::class, 'testChemistryDetection'])
                ->name('api.chimie.detect')
                ->where('question', '[0-9]+');

            // Health check du service chimie
            Route::get('/health', [ChimieController::class, 'healthCheck'])
                ->name('api.chimie.health');
        });
    });

    // 2. ROUTES WEB CHIMIE (interface utilisateur)
    Route::prefix('chimie')->name('chimie.')->group(function () {

        // Page d'évaluation chimie
        Route::get('/evaluation/{question}', [ChimieController::class, 'show'])
            ->name('evaluation.show')
            ->where('question', '[0-9]+');

        // Réévaluation forcée (POST)
        Route::post('/force-eval/{question}', [ChimieController::class, 'forceReEvaluation'])
            ->name('force.eval')
            ->where('question', '[0-9]+');
    });

    // 3. ROUTES AJAX SPÉCIFIQUES CHIMIE
    Route::prefix('ajax/chimie')->group(function () {

        // AJAX: Démarrer évaluation chimie
        Route::post('/start-eval/{question}', function ($questionId) {
            try {
                $question = \App\Models\Question::where('user_id', auth()->id())->findOrFail($questionId);
                $chimieController = app(ChimieController::class);

                return $chimieController->evaluateChemistryQuestion(request(), $question);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur AJAX évaluation chimie: ' . $e->getMessage()
                ], 500);
            }
        })->name('ajax.chimie.start.eval')->where('question', '[0-9]+');

        // AJAX: Vérifier statut chimie
        Route::get('/check-status/{question}', function ($questionId) {
            try {
                $question = \App\Models\Question::where('user_id', auth()->id())->findOrFail($questionId);
                $chimieController = app(ChimieController::class);

                return $chimieController->getChemistryEvaluationStatus($question);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur AJAX statut chimie: ' . $e->getMessage()
                ], 500);
            }
        })->name('ajax.chimie.check.status')->where('question', '[0-9]+');

        // AJAX: Test détection chimie rapide
        Route::get('/quick-detect/{question}', function ($questionId) {
            try {
                $question = \App\Models\Question::where('user_id', auth()->id())->findOrFail($questionId);
                $chimieService = app(\App\Services\ChimieEvaluationService::class);

                $isChemistry = $chimieService->isChemistryQuestion($question);

                return response()->json([
                    'success' => true,
                    'question_id' => $question->id,
                    'is_chemistry' => $isChemistry,
                    'domain' => $question->domain->name ?? 'N/A',
                    'content_preview' => substr($question->content, 0, 100) . '...'
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur détection: ' . $e->getMessage(),
                    'question_id' => $questionId
                ], 500);
            }
        })->name('ajax.chimie.quick.detect')->where('question', '[0-9]+');
    });

    // 4. ROUTES DE DEBUG CHIMIE (uniquement en développement)
    Route::prefix('debug/chimie')->group(function () {

        // Test complet d'une question
        Route::get('/full-test/{question}', function ($questionId) {
            if (!app()->environment('local', 'development')) {
                abort(404);
            }

            try {
                $question = \App\Models\Question::with(['domain', 'iaResponses', 'evaluation'])
                    ->where('user_id', auth()->id())
                    ->findOrFail($questionId);

                $chimieService = app(\App\Services\ChimieEvaluationService::class);

                // Tests complets
                $tests = [
                    'question_info' => [
                        'id' => $question->id,
                        'content_length' => strlen($question->content),
                        'domain' => $question->domain->name ?? 'N/A',
                        'responses_count' => $question->iaResponses->count(),
                        'has_evaluation' => $question->evaluation !== null
                    ],
                    'service_tests' => [
                        'service_exists' => class_exists(\App\Services\ChimieEvaluationService::class),
                        'service_instantiated' => $chimieService !== null,
                        'has_isChemistryQuestion' => method_exists($chimieService, 'isChemistryQuestion'),
                        'has_evaluateQuestion' => method_exists($chimieService, 'evaluateQuestion')
                    ],
                    'detection_test' => [],
                    'readiness_test' => []
                ];

                // Test de détection
                try {
                    $isChemistry = $chimieService->isChemistryQuestion($question);
                    $tests['detection_test'] = [
                        'is_chemistry' => $isChemistry,
                        'status' => 'success'
                    ];
                } catch (\Exception $e) {
                    $tests['detection_test'] = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }

                // Test de préparation
                try {
                    $hasAllResponses = $question->hasAllAIResponses();
                    $tests['readiness_test'] = [
                        'has_all_responses' => $hasAllResponses,
                        'missing_responses' => $hasAllResponses ? [] : array_diff(['gpt4', 'deepseek', 'qwen'], $question->iaResponses->pluck('model')->toArray()),
                        'can_evaluate' => $hasAllResponses && ($tests['detection_test']['is_chemistry'] ?? false),
                        'status' => 'success'
                    ];
                } catch (\Exception $e) {
                    $tests['readiness_test'] = [
                        'status' => 'error',
                        'message' => $e->getMessage()
                    ];
                }

                return response()->json([
                    'full_test_results' => $tests,
                    'recommendation' => $tests['readiness_test']['can_evaluate'] ?? false ?
                        'Question prête pour évaluation chimie' :
                        'Question non prête ou problème détecté',
                    'next_action' => $tests['readiness_test']['can_evaluate'] ?? false ?
                        'POST /api/chimie/evaluate/' . $question->id :
                        'Corriger les problèmes identifiés'
                ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Erreur lors du test complet',
                    'message' => $e->getMessage(),
                    'trace' => explode("\n", $e->getTraceAsString())
                ], 500);
            }
        })->name('debug.chimie.full.test')->where('question', '[0-9]+');

        // Lister toutes les routes chimie disponibles
        Route::get('/routes', function () {
            if (!app()->environment('local', 'development')) {
                abort(404);
            }

            $routes = collect(\Illuminate\Support\Facades\Route::getRoutes())
                ->filter(function ($route) {
                    return str_contains($route->getName() ?? '', 'chimie') ||
                        str_contains($route->uri(), 'chimie');
                })
                ->map(function ($route) {
                    return [
                        'name' => $route->getName(),
                        'uri' => $route->uri(),
                        'methods' => $route->methods(),
                        'action' => $route->getActionName()
                    ];
                })
                ->values();

            return response()->json([
                'available_chemistry_routes' => $routes,
                'total_routes' => count($routes),
                'test_urls' => [
                    'health_check' => route('api.chimie.health'),
                    'detect_example' => '/api/chimie/detect/{question_id}',
                    'evaluate_example' => '/api/chimie/evaluate/{question_id}',
                    'status_example' => '/api/chimie/status/{question_id}'
                ]
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        })->name('debug.chimie.routes');

        // Test des services externes (Wolfram, DeepL)
        Route::get('/test-externals', function () {
            if (!app()->environment('local', 'development')) {
                abort(404);
            }

            $tests = [];

            // Test Wolfram Alpha
            try {
                $wolfram = app(\App\Services\WolframAlphaService::class);
                $tests['wolfram'] = [
                    'service_exists' => true,
                    'can_instantiate' => $wolfram !== null,
                    'test_query' => 'H2O',
                    'status' => 'ready_for_test'
                ];
            } catch (\Exception $e) {
                $tests['wolfram'] = [
                    'service_exists' => false,
                    'error' => $e->getMessage()
                ];
            }

            // Test DeepL
            try {
                $deepl = app(\App\Services\DeepLService::class);
                $tests['deepl'] = [
                    'service_exists' => true,
                    'can_instantiate' => $deepl !== null,
                    'test_text' => 'Quelle est la formule de l\'eau ?',
                    'status' => 'ready_for_test'
                ];
            } catch (\Exception $e) {
                $tests['deepl'] = [
                    'service_exists' => false,
                    'error' => $e->getMessage()
                ];
            }

            // Test OpenRouter
            try {
                $openrouter = app(\App\Services\OpenRouterService::class);
                $tests['openrouter'] = [
                    'service_exists' => true,
                    'can_instantiate' => $openrouter !== null,
                    'status' => 'ready_for_test'
                ];
            } catch (\Exception $e) {
                $tests['openrouter'] = [
                    'service_exists' => false,
                    'error' => $e->getMessage()
                ];
            }

            return response()->json([
                'external_services_test' => $tests,
                'all_services_ready' => !collect($tests)->contains(function ($test) {
                    return !($test['service_exists'] ?? false);
                })
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        })->name('debug.chimie.externals');
    });

    // 5. ROUTE DE DIAGNOSTIC GÉNÉRAL
    Route::get('/diagnostic/chimie', function () {
        try {
            // Collecter toutes les informations de diagnostic
            $diagnostic = [
                'timestamp' => now(),
                'user_id' => auth()->id(),
                'environment' => app()->environment(),
                'chemistry_service' => [
                    'class_exists' => class_exists(\App\Services\ChimieEvaluationService::class),
                    'can_instantiate' => false,
                    'methods' => []
                ],
                'controller' => [
                    'class_exists' => class_exists(\App\Http\Controllers\ChimieController::class),
                    'methods' => []
                ],
                'routes' => [
                    'api_evaluate' => route('api.chimie.evaluate', ['question' => 'ID']),
                    'api_status' => route('api.chimie.status', ['question' => 'ID']),
                    'api_health' => route('api.chimie.health')
                ],
                'database' => [
                    'questions_with_chemistry_evaluation' => 0,
                    'total_questions' => 0
                ]
            ];

            // Test du service
            try {
                $chimieService = app(\App\Services\ChimieEvaluationService::class);
                $diagnostic['chemistry_service']['can_instantiate'] = true;
                $diagnostic['chemistry_service']['methods'] = [
                    'isChemistryQuestion' => method_exists($chimieService, 'isChemistryQuestion'),
                    'evaluateQuestion' => method_exists($chimieService, 'evaluateQuestion'),
                    'analyzeChemistryQuestion' => method_exists($chimieService, 'analyzeChemistryQuestion')
                ];
            } catch (\Exception $e) {
                $diagnostic['chemistry_service']['error'] = $e->getMessage();
            }

            // Test du contrôleur
            try {
                $controllerMethods = get_class_methods(\App\Http\Controllers\ChimieController::class);
                $diagnostic['controller']['methods'] = $controllerMethods;
            } catch (\Exception $e) {
                $diagnostic['controller']['error'] = $e->getMessage();
            }

            // Stats base de données
            try {
                $diagnostic['database']['total_questions'] = \App\Models\Question::count();
                $diagnostic['database']['questions_with_chemistry_evaluation'] = \App\Models\Evaluation::where('evaluation_type', 'chemistry')->count();
            } catch (\Exception $e) {
                $diagnostic['database']['error'] = $e->getMessage();
            }

            return response()->json([
                'diagnostic_complete' => $diagnostic,
                'status' => 'success',
                'ready_for_chemistry' => $diagnostic['chemistry_service']['can_instantiate'] &&
                    ($diagnostic['chemistry_service']['methods']['evaluateQuestion'] ?? false)
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            return response()->json([
                'diagnostic_error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    })->name('diagnostic.chimie');
});

// === 6. CORRECTION DE LA ROUTE FALLBACK ===
// Modifier la route fallback existante pour inclure les routes chimie
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route non trouvée',
        'available_routes' => [
            'main' => '/ia',
            'evaluate_standard' => '/questions/{id}/evaluate',
            'evaluate_chemistry' => '/api/chimie/evaluate/{id}',
            'chemistry_status' => '/api/chimie/status/{id}',
            'chemistry_health' => '/api/chimie/health',
            'stats' => '/evaluations/stats',
            'diagnostic' => '/diagnostic/chimie',
            'debug' => '/debug/chimie/routes (dev only)'
        ],
        'chemistry_routes' => [
            'POST /api/chimie/evaluate/{question}' => 'Évaluer une question de chimie',
            'GET /api/chimie/status/{question}' => 'Statut évaluation chimie',
            'GET /api/chimie/health' => 'Health check service chimie',
            'GET /diagnostic/chimie' => 'Diagnostic complet du système chimie'
        ]
    ], 404);
});


Route::get('/ia-force', function() {
    $domains = \App\Models\Domain::all();
    return view('ia.index', compact('domains'));
})->middleware('auth')->name('ia.force');







Route::get('/test-chimie-service', function() {
    try {
        // Test 1: Vérifier si la classe existe
        $classExists = class_exists(\App\Services\ChimieEvaluationService::class);

        // Test 2: Essayer d'instancier le service
        $service = app(\App\Services\ChimieEvaluationService::class);

        // Test 3: Vérifier les méthodes
        $methods = [
            'isChemistryQuestion' => method_exists($service, 'isChemistryQuestion'),
            'evaluateQuestion' => method_exists($service, 'evaluateQuestion'),
        ];

        return response()->json([
            'success' => true,
            'class_exists' => $classExists,
            'service_instantiated' => $service !== null,
            'service_class' => get_class($service),
            'methods' => $methods
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
})->middleware('auth');
