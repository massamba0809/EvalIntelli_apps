<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Services\ChimieEvaluationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Contrôleur Chimie corrigé avec gestion d'erreurs renforcée
 */
class ChimieController extends Controller
{
    protected ChimieEvaluationService $chimieService;

    public function __construct(ChimieEvaluationService $chimieService)
    {
        $this->chimieService = $chimieService;
    }

    /**
     * Évalue une question de chimie avec gestion d'erreurs améliorée
     */
    public function evaluateChemistryQuestion(Request $request, Question $question)
    {
        try {
            // Vérifications de sécurité
            if ($question->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à cette question'
                ], 403);
            }

            // Charger les relations nécessaires
            $question->load(['domain', 'iaResponses', 'evaluation']);

            // Vérifier si une évaluation existe déjà
            if ($question->evaluation && in_array($question->evaluation->evaluation_type, ['chemistry', 'chemistry_fallback'])) {
                Log::info('✅ ÉVALUATION CHIMIE DÉJÀ EXISTANTE', [
                    'question_id' => $question->id,
                    'evaluation_type' => $question->evaluation->evaluation_type
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Évaluation chimie déjà disponible',
                    'evaluation' => $question->evaluation,
                    'from_cache' => true
                ]);
            }

            // Vérifier les réponses IA (avec meilleur message d'erreur)
            if (!$question->hasAllAIResponses()) {
                $missingResponses = $this->getMissingResponses($question);

                return response()->json([
                    'success' => false,
                    'message' => 'Réponses IA incomplètes',
                    'details' => [
                        'responses_count' => $question->iaResponses->count(),
                        'missing_responses' => $missingResponses,
                        'required_responses' => ['gpt4', 'deepseek', 'qwen']
                    ]
                ], 400);
            }

            Log::info('🧪 DÉBUT ÉVALUATION CHIMIE', [
                'question_id' => $question->id,
                'user_id' => Auth::id(),
                'domain' => $question->domain->name ?? 'N/A',
                'responses_count' => $question->iaResponses->count()
            ]);

            // Lancer l'évaluation avec try-catch interne
            $evaluation = $this->chimieService->evaluateQuestion($question);

            if ($evaluation['success']) {
                Log::info('✅ ÉVALUATION CHIMIE RÉUSSIE', [
                    'question_id' => $question->id,
                    'evaluation_id' => $evaluation['evaluation']->id,
                    'best_ai' => $evaluation['evaluation']->meilleure_ia,
                    'evaluation_type' => $evaluation['evaluation']->evaluation_type,
                    'wolfram_status' => $evaluation['evaluation']->wolfram_status ?? 'N/A'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Évaluation chimie terminée avec succès',
                    'evaluation' => $evaluation['evaluation'],
                    'details' => $evaluation['details'] ?? null,
                    'evaluation_type' => $evaluation['evaluation']->evaluation_type
                ]);
            } else {
                Log::error('❌ ÉCHEC ÉVALUATION CHIMIE', [
                    'question_id' => $question->id,
                    'error' => $evaluation['error'] ?? 'Erreur inconnue'
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $evaluation['error'] ?? 'Erreur lors de l\'évaluation',
                    'details' => $evaluation['details'] ?? null,
                    'fallback_attempted' => true
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('💥 EXCEPTION CRITIQUE ÉVALUATION CHIMIE', [
                'question_id' => $question->id,
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur critique lors de l\'évaluation chimie',
                'error_details' => [
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ],
                'suggestion' => 'Veuillez réessayer ou utiliser l\'évaluation standard'
            ], 500);
        }
    }

    /**
     * Obtient le statut d'une évaluation chimie avec gestion d'erreurs
     */
    public function getChemistryEvaluationStatus(Question $question)
    {
        try {
            // Vérification de sécurité
            if ($question->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à cette question'
                ], 403);
            }

            // Charger les relations nécessaires
            $question->load(['domain', 'iaResponses', 'evaluation']);

            // Détection chimie sécurisée
            $isChemistry = false;
            try {
                $isChemistry = $this->chimieService->isChemistryQuestion($question);
            } catch (\Exception $e) {
                Log::warning('⚠️ Erreur détection chimie dans status', [
                    'question_id' => $question->id,
                    'error' => $e->getMessage()
                ]);
            }

            $status = [
                'question_id' => $question->id,
                'is_chemistry' => $isChemistry,
                'has_all_responses' => $question->hasAllAIResponses(),
                'responses_count' => $question->iaResponses->count(),
                'has_evaluation' => $question->evaluation !== null,
                'evaluation_type' => $question->evaluation?->evaluation_type,
                'is_chemistry_evaluation' => $question->evaluation &&
                    in_array($question->evaluation->evaluation_type, ['chemistry', 'chemistry_fallback'])
            ];

            // Ajouter les détails de l'évaluation si elle existe
            if ($question->evaluation && in_array($question->evaluation->evaluation_type, ['chemistry', 'chemistry_fallback'])) {
                $status['evaluation'] = [
                    'id' => $question->evaluation->id,
                    'best_ai' => $question->evaluation->meilleure_ia,
                    'scores' => [
                        'gpt4' => $question->evaluation->note_gpt4,
                        'deepseek' => $question->evaluation->note_deepseek,
                        'qwen' => $question->evaluation->note_qwen
                    ],
                    'wolfram_status' => $question->evaluation->wolfram_status ?? 'unknown',
                    'has_wolfram_reference' => !empty($question->evaluation->wolfram_reference),
                    'evaluation_type' => $question->evaluation->evaluation_type,
                    'is_fallback' => $question->evaluation->evaluation_type === 'chemistry_fallback',
                    'created_at' => $question->evaluation->created_at
                ];
            }

            return response()->json([
                'success' => true,
                'status' => $status
            ]);

        } catch (\Exception $e) {
            Log::error('💥 ERREUR STATUT CHIMIE', [
                'question_id' => $question->id,
                'exception' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du statut',
                'error_details' => [
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    /**
     * Affiche la page d'évaluation chimie avec gestion d'erreurs
     */
    public function show(Question $question)
    {
        try {
            // Vérification de sécurité
            if ($question->user_id !== Auth::id()) {
                return redirect()->route('ia.index')->with('error', 'Accès non autorisé à cette question');
            }

            // Charger les relations nécessaires
            $question->load(['domain', 'iaResponses', 'evaluation']);

            // Vérification chimie sécurisée
            $isChemistry = false;
            try {
                $isChemistry = $this->chimieService->isChemistryQuestion($question);
            } catch (\Exception $e) {
                Log::warning('⚠️ Erreur détection chimie dans show', [
                    'question_id' => $question->id,
                    'error' => $e->getMessage()
                ]);
            }

            if (!$isChemistry) {
                return redirect()->route('ia.results.by.id', ['question' => $question->id])
                    ->with('info', 'Cette question n\'est pas identifiée comme une question de chimie.');
            }

            // Vérifier les réponses IA
            if (!$question->hasAllAIResponses()) {
                $missingResponses = $this->getMissingResponses($question);

                return redirect()->route('ia.results.by.id', ['question' => $question->id])
                    ->with('warning', 'Toutes les réponses IA ne sont pas encore disponibles. Manquantes: ' . implode(', ', $missingResponses));
            }

            Log::info('🧪 AFFICHAGE PAGE ÉVALUATION CHIMIE', [
                'question_id' => $question->id,
                'domain' => $question->domain->name ?? 'N/A',
                'has_evaluation' => $question->evaluation ? true : false,
                'evaluation_type' => $question->evaluation?->evaluation_type
            ]);

            // Utiliser la vue d'évaluation générique avec type chimie
            return view('ia.evaluation', [
                'question' => $question,
                'evaluation' => $question->evaluation,
                'domain_type' => 'chemistry'
            ]);

        } catch (\Exception $e) {
            Log::error('💥 ERREUR AFFICHAGE CHIMIE', [
                'question_id' => $question->id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('ia.index')->with('error', 'Erreur lors de l\'affichage de l\'évaluation chimie');
        }
    }

    /**
     * Test de détection de question chimie (debug) - Version sécurisée
     */
    public function testChemistryDetection(Question $question)
    {
        if (!app()->environment('local', 'development')) {
            abort(404);
        }

        try {
            $detection = $this->chimieService->analyzeChemistryQuestion($question->content);
            $isChemistry = $this->chimieService->isChemistryQuestion($question);

            return response()->json([
                'question_id' => $question->id,
                'content' => $question->content,
                'domain' => $question->domain->name ?? 'N/A',
                'detection' => $detection,
                'is_chemistry' => $isChemistry,
                'debug_info' => [
                    'service_exists' => class_exists(\App\Services\ChimieEvaluationService::class),
                    'method_exists' => method_exists($this->chimieService, 'analyzeChemistryQuestion'),
                    'content_length' => strlen($question->content)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du test de détection',
                'message' => $e->getMessage(),
                'question_id' => $question->id,
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Méthode utilitaire pour identifier les réponses manquantes
     */
    protected function getMissingResponses(Question $question): array
    {
        $requiredModels = ['gpt4', 'deepseek', 'qwen'];
        $existingModels = $question->iaResponses->pluck('model')->toArray();

        return array_diff($requiredModels, $existingModels);
    }

    /**
     * Force une réévaluation chimie (debug uniquement)
     */
    public function forceReEvaluation(Question $question)
    {
        if (!app()->environment('local', 'development')) {
            abort(404);
        }

        try {
            // Supprimer l'évaluation existante
            if ($question->evaluation) {
                $question->evaluation->delete();
                Log::info('🗑️ Évaluation existante supprimée pour réévaluation', [
                    'question_id' => $question->id
                ]);
            }

            // Relancer l'évaluation
            return $this->evaluateChemistryQuestion(request(), $question);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la réévaluation forcée',
                'message' => $e->getMessage(),
                'question_id' => $question->id
            ], 500);
        }
    }

    /**
     * Endpoint de santé pour vérifier le service chimie
     */
    public function healthCheck()
    {
        try {
            $checks = [
                'service_loaded' => isset($this->chimieService),
                'service_class' => get_class($this->chimieService),
                'required_methods' => [
                    'evaluateQuestion' => method_exists($this->chimieService, 'evaluateQuestion'),
                    'isChemistryQuestion' => method_exists($this->chimieService, 'isChemistryQuestion'),
                ],
                'dependencies' => [
                    'openrouter' => class_exists(\App\Services\OpenRouterService::class),
                    'wolfram' => class_exists(\App\Services\WolframAlphaService::class),
                    'deepl' => class_exists(\App\Services\DeepLService::class),
                ]
            ];

            $allChecksPass = $checks['service_loaded'] &&
                array_reduce($checks['required_methods'], function($carry, $item) { return $carry && $item; }, true) &&
                array_reduce($checks['dependencies'], function($carry, $item) { return $carry && $item; }, true);

            return response()->json([
                'status' => $allChecksPass ? 'healthy' : 'unhealthy',
                'checks' => $checks,
                'timestamp' => now()
            ], $allChecksPass ? 200 : 500);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
