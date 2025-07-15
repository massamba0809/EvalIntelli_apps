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
    Route::prefix('chimie')->group(function () {
        // Routes dédiées au domaine chimie (optionnelles, pour usage avancé)
        Route::post('/evaluate/{question}', [ChimieController::class, 'evaluateChemistryQuestion'])
            ->name('chimie.evaluate')
            ->where('question', '[0-9]+');

        Route::get('/status/{question}', [ChimieController::class, 'getChemistryEvaluationStatus'])
            ->name('chimie.status')
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

// === ROUTES DE DÉVELOPPEMENT/DEBUG (À SUPPRIMER EN PRODUCTION) ===
if (app()->environment('local', 'development')) {
    Route::prefix('debug')->middleware('auth')->group(function () {
        // Test de détection de domaine
        Route::get('/detect/{question}', function($questionId) {
            $question = \App\Models\Question::with('domain')->findOrFail($questionId);

            return response()->json([
                'question_id' => $question->id,
                'domain' => $question->domain->name,
                'detections' => [
                    'is_programming' => $question->isProgrammingQuestion(),
                    'is_mathematical' => $question->isMathematicalQuestion(),
                    'is_translation' => $question->isTranslationQuestion(),
                    'is_chemistry' => $question->isChemistryQuestion(),
                    'evaluation_type' => $question->getEvaluationType(),
                    'is_evaluable' => $question->isEvaluableQuestion()
                ]
            ]);
        })->name('debug.detect')
            ->where('question', '[0-9]+');

        // Test des services
        Route::get('/test/wolfram/{query}', function($query) {
            $wolfram = app(\App\Services\WolframAlphaService::class);
            return response()->json($wolfram->querySimple($query));
        })->name('debug.wolfram');

        Route::get('/test/deepl/{text}', function($text) {
            $deepl = app(\App\Services\DeepLService::class);
            return response()->json($deepl->translate($text, 'EN', 'FR'));
        })->name('debug.deepl');

        // Statistiques détaillées pour debug
        Route::get('/stats/detailed', function() {
            $evaluationService = app(\App\Services\EvaluationService::class);
            return response()->json($evaluationService->getEvaluationDashboard());
        })->name('debug.stats');
    });
}

// === GESTION D'ERREURS PERSONNALISÉES ===
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route non trouvée',
        'available_routes' => [
            'main' => '/ia',
            'evaluate' => '/questions/{id}/evaluate',
            'stats' => '/evaluations/stats',
            'api' => '/api/user/questions'
        ]
    ], 404);
});
