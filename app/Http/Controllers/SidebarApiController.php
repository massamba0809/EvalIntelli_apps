<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Question;
use App\Models\Evaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SidebarApiController extends Controller
{
    /**
     * Récupère les questions de l'utilisateur connecté pour le sidebar
     */
    public function getUserQuestions()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            // Récupérer les questions avec les relations nécessaires
            $questions = Question::with(['domain', 'evaluation', 'iaResponses'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'content' => $question->content,
                        'created_at' => $question->created_at,
                        'domain_id' => $question->domain_id,
                        'domain_name' => $question->domain->name ?? 'Domaine supprimé',
                        'is_programming' => $question->isProgrammingQuestion(),
                        'is_mathematics' => $question->isMathematicalQuestion(),
                        'is_evaluable' => $question->isEvaluableQuestion(),
                        'evaluation_type' => $question->getEvaluationType(),
                        'has_evaluation' => $question->evaluation ? true : false,
                        'responses_count' => $question->iaResponses->count(),
                        'evaluation_status' => $this->getQuestionEvaluationStatus($question),

                        // AJOUT : Données d'évaluation pour navigation directe
                        'evaluation_data' => $question->evaluation ? [
                            'id' => $question->evaluation->id,
                            'note_gpt4' => $question->evaluation->note_gpt4,
                            'note_deepseek' => $question->evaluation->note_deepseek,
                            'note_qwen' => $question->evaluation->note_qwen,
                            'meilleure_ia' => $question->evaluation->meilleure_ia,
                            'evaluation_type' => $question->evaluation->evaluation_type ?? 'programming',
                            'has_wolfram_reference' => method_exists($question->evaluation, 'hasWolframReference')
                                ? $question->evaluation->hasWolframReference() : false,
                        ] : null,
                    ];
                });

            // Récupérer tous les domaines pour le filtre
            $domains = Domain::all(['id', 'name'])->map(function ($domain) {
                return [
                    'id' => $domain->id,
                    'name' => $domain->name,
                ];
            });

            return response()->json([
                'success' => true,
                'questions' => $questions,
                'domains' => $domains,
                'total' => $questions->count(),
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des questions utilisateur', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du chargement des questions',
                'questions' => [],
                'domains' => [],
            ], 500);
        }
    }


    /**
     * Récupère les détails d'une question spécifique
     */
    public function getQuestionDetails(Question $question)
    {
        try {
            // Vérifier que la question appartient à l'utilisateur connecté
            if ($question->user_id !== Auth::id()) {
                return response()->json(['error' => 'Non autorisé'], 403);
            }

            // Charger toutes les relations AVEC les détails d'évaluation
            $question->load(['domain', 'iaResponses', 'evaluation', 'user']);

            $questionData = [
                'id' => $question->id,
                'content' => $question->content,
                'created_at' => $question->created_at,
                'updated_at' => $question->updated_at,
                'domain' => [
                    'id' => $question->domain->id,
                    'name' => $question->domain->name,
                    'slug' => $question->domain->slug,
                ],
                'is_programming' => $question->isProgrammingQuestion(),
                'is_mathematics' => $question->isMathematicalQuestion(),
                'is_evaluable' => $question->isEvaluableQuestion(),
                'evaluation_type' => $question->getEvaluationType(),

                // DONNÉES COMPLÈTES DES RÉPONSES
                'responses' => $question->iaResponses->map(function ($response) {
                    return [
                        'id' => $response->id,
                        'model_name' => $response->model_name,
                        'response' => $response->response,
                        'cleaned_response' => $this->cleanResponse($response->response),
                        'token_usage' => $response->token_usage,
                        'response_time' => $response->response_time,
                        'created_at' => $response->created_at,
                    ];
                }),

                // DONNÉES COMPLÈTES D'ÉVALUATION avec détails
                'evaluation' => $question->evaluation ? [
                    'id' => $question->evaluation->id,
                    'note_gpt4' => $question->evaluation->note_gpt4,
                    'note_deepseek' => $question->evaluation->note_deepseek,
                    'note_qwen' => $question->evaluation->note_qwen,
                    'meilleure_ia' => $question->evaluation->meilleure_ia,
                    'commentaire_global' => $question->evaluation->commentaire_global,
                    'evaluation_type' => $question->evaluation->evaluation_type ?? 'programming',
                    'created_at' => $question->evaluation->created_at,
                    'is_complete' => $question->evaluation->isComplete(),

                    // IMPORTANT : Inclure les détails d'évaluation pour chaque IA
                    'evaluation_gpt4' => $this->parseEvaluationJson($question->evaluation->evaluation_gpt4),
                    'evaluation_deepseek' => $this->parseEvaluationJson($question->evaluation->evaluation_deepseek),
                    'evaluation_qwen' => $this->parseEvaluationJson($question->evaluation->evaluation_qwen),

                    // Données Wolfram Alpha
                    'wolfram_reference' => $question->evaluation->wolfram_reference,
                    'wolfram_response_time' => $question->evaluation->wolfram_response_time,
                    'has_wolfram_reference' => method_exists($question->evaluation, 'hasWolframReference')
                        ? $question->evaluation->hasWolframReference() : false,

                    // Métadonnées
                    'token_usage_evaluation' => $question->evaluation->token_usage_evaluation,
                    'response_time_evaluation' => $question->evaluation->response_time_evaluation,

                    // Scores calculés
                    'best_score' => max(
                        $question->evaluation->note_gpt4 ?? 0,
                        $question->evaluation->note_deepseek ?? 0,
                        $question->evaluation->note_qwen ?? 0
                    ),
                    'average_score' => $this->calculateAverageScore($question->evaluation),
                ] : null,

                'stats' => [
                    'responses_count' => $question->iaResponses->count(),
                    'has_evaluation' => $question->evaluation ? true : false,
                    'evaluation_status' => $this->getQuestionEvaluationStatus($question),
                    'total_tokens' => $question->iaResponses->sum('token_usage'),
                    'average_response_time' => $question->iaResponses->avg('response_time'),
                ]
            ];

            return response()->json([
                'success' => true,
                'question' => $questionData,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des détails de la question', [
                'question_id' => $question->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du chargement des détails'
            ], 500);
        }
    }

    /**
     * Récupère les statistiques de l'utilisateur pour le sidebar
     */
    public function getUserStats()
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            $totalQuestions = Question::where('user_id', $user->id)->count();
            $programmingQuestions = Question::where('user_id', $user->id)
                ->whereHas('domain', function($query) {
                    $query->where('name', 'LIKE', '%programmation%')
                        ->orWhere('name', 'LIKE', '%programming%')
                        ->orWhere('name', 'LIKE', '%code%');
                })->count();

            $evaluatedQuestions = Question::where('user_id', $user->id)
                ->whereHas('evaluation')->count();

            $thisWeekQuestions = Question::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subWeek())
                ->count();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_questions' => $totalQuestions,
                    'programming_questions' => $programmingQuestions,
                    'evaluated_questions' => $evaluatedQuestions,
                    'this_week_questions' => $thisWeekQuestions,
                    'completion_rate' => $programmingQuestions > 0 ?
                        round(($evaluatedQuestions / $programmingQuestions) * 100, 1) : 0,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des statistiques utilisateur', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du chargement des statistiques'
            ], 500);
        }
    }

    /**
     * Recherche dans les questions de l'utilisateur
     */
    public function searchUserQuestions(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            $validated = $request->validate([
                'query' => 'required|string|min:2|max:100',
                'domain_id' => 'nullable|exists:domains,id',
                'type' => 'nullable|in:programming,other,all',
                'limit' => 'nullable|integer|min:1|max:50'
            ]);

            $query = Question::with(['domain', 'evaluation'])
                ->where('user_id', $user->id)
                ->where('content', 'LIKE', '%' . $validated['query'] . '%');

            // Filtre par domaine
            if (!empty($validated['domain_id'])) {
                $query->where('domain_id', $validated['domain_id']);
            }

            // Filtre par type
            if (!empty($validated['type']) && $validated['type'] !== 'all') {
                if ($validated['type'] === 'programming') {
                    $query->whereHas('domain', function($q) {
                        $q->where('name', 'LIKE', '%programmation%')
                            ->orWhere('name', 'LIKE', '%programming%')
                            ->orWhere('name', 'LIKE', '%code%');
                    });
                } else {
                    $query->whereDoesntHave('domain', function($q) {
                        $q->where('name', 'LIKE', '%programmation%')
                            ->orWhere('name', 'LIKE', '%programming%')
                            ->orWhere('name', 'LIKE', '%code%');
                    });
                }
            }

            $questions = $query->orderBy('created_at', 'desc')
                ->limit($validated['limit'] ?? 20)
                ->get()
                ->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'content' => $question->content,
                        'created_at' => $question->created_at,
                        'domain_id' => $question->domain_id,
                        'domain_name' => $question->domain->name ?? 'Domaine supprimé',
                        'is_programming' => $question->isProgrammingQuestion(),
                        'has_evaluation' => $question->evaluation ? true : false,
                        'responses_count' => $question->iaResponses->count(),
                        'evaluation_status' => $this->getQuestionEvaluationStatus($question),
                    ];
                });

            return response()->json([
                'success' => true,
                'questions' => $questions,
                'total' => $questions->count(),
                'query' => $validated['query']
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la recherche de questions', [
                'user_id' => Auth::id(),
                'query' => $request->input('query'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la recherche'
            ], 500);
        }
    }

    /**
     * Récupère les questions récentes pour le dashboard
     */
    public function getRecentQuestions(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $limit = min(max(1, $limit), 50); // Entre 1 et 50

            $questions = Question::with(['domain', 'evaluation'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($question) {
                    return $this->formatQuestionForApi($question);
                });

            return response()->json([
                'success' => true,
                'questions' => $questions,
                'total' => $questions->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des questions récentes', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors du chargement'
            ], 500);
        }
    }

    /**
     * Supprime une question de l'historique
     */
    public function deleteQuestion(Question $question)
    {
        try {
            // Vérifier que la question appartient à l'utilisateur connecté
            if ($question->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Non autorisé à supprimer cette question'
                ], 403);
            }

            // Sauvegarder les informations pour le log
            $questionInfo = [
                'id' => $question->id,
                'content' => Str::limit($question->content, 100),
                'domain' => $question->domain->name ?? 'Inconnu',
                'user_id' => $question->user_id,
            ];

            // Supprimer la question (cascade pour les réponses et évaluations)
            $question->delete();

            \Log::info('Question supprimée avec succès', $questionInfo);

            return response()->json([
                'success' => true,
                'message' => 'Question supprimée avec succès',
                'deleted_question_id' => $questionInfo['id']
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de la question', [
                'question_id' => $question->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression de la question'
            ], 500);
        }
    }

    /**
     * Supprime plusieurs questions en lot
     */
    public function deleteBulkQuestions(Request $request)
    {
        try {
            $validated = $request->validate([
                'question_ids' => 'required|array|min:1|max:50',
                'question_ids.*' => 'required|integer|exists:questions,id'
            ]);

            $user = Auth::user();
            $questionIds = $validated['question_ids'];

            // Vérifier que toutes les questions appartiennent à l'utilisateur
            $questions = Question::whereIn('id', $questionIds)
                ->where('user_id', $user->id)
                ->get();

            if ($questions->count() !== count($questionIds)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Certaines questions n\'existent pas ou ne vous appartiennent pas'
                ], 403);
            }

            // Supprimer les questions
            $deletedCount = Question::whereIn('id', $questionIds)
                ->where('user_id', $user->id)
                ->delete();

            \Log::info('Suppression en lot de questions', [
                'user_id' => $user->id,
                'deleted_count' => $deletedCount,
                'question_ids' => $questionIds
            ]);

            return response()->json([
                'success' => true,
                'message' => "✅ {$deletedCount} questions supprimées avec succès",
                'deleted_count' => $deletedCount,
                'deleted_ids' => $questionIds
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression en lot', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression des questions'
            ], 500);
        }
    }

    /**
     * Vide complètement l'historique de l'utilisateur
     */
    public function clearAllHistory(Request $request)
    {
        try {
            $user = Auth::user();

            // Validation pour sécurité (mot de passe ou confirmation)
            $validated = $request->validate([
                'confirmation' => 'required|string|in:CONFIRM_DELETE_ALL'
            ]);

            // Compter les questions avant suppression
            $totalQuestions = Question::where('user_id', $user->id)->count();

            if ($totalQuestions === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune question à supprimer'
                ]);
            }

            // Supprimer toutes les questions de l'utilisateur
            $deletedCount = Question::where('user_id', $user->id)->delete();

            \Log::warning('Historique complet supprimé', [
                'user_id' => $user->id,
                'deleted_count' => $deletedCount,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => "🗑️ Historique complet supprimé ({$deletedCount} questions)",
                'deleted_count' => $deletedCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression complète de l\'historique', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la suppression de l\'historique'
            ], 500);
        }
    }

    /**
     * Obtient le statut d'évaluation d'une question
     */
    protected function getQuestionEvaluationStatus(Question $question): string
    {
        if (!$question->isProgrammingQuestion()) {
            return 'not_applicable';
        }

        if ($question->evaluation) {
            return $question->evaluation->isComplete() ? 'completed' : 'partial';
        }

        if ($question->hasAllAIResponses()) {
            return 'pending';
        }

        return 'waiting_responses';
    }

    /**
     * Nettoie la réponse en supprimant les commentaires
     */
    protected function cleanResponse(string $response): string
    {
        $response = preg_replace('/<!--.*?-->/s', '', $response);
        $response = preg_replace('/\/\/.*$/m', '', $response);
        $response = preg_replace('/\/\*.*?\*\//s', '', $response);
        $response = preg_replace('/\[\s*\/\/.*?\]/s', '', $response);
        $response = preg_replace('/\[Note:.*?\]/si', '', $response);
        $response = preg_replace('/\[Commentaire:.*?\]/si', '', $response);
        $response = preg_replace('/\[Comment:.*?\]/si', '', $response);

        $lines = explode("\n", $response);
        $cleanedLines = array_filter($lines, function($line) {
            $trimmedLine = trim($line);
            return !empty($trimmedLine) &&
                !preg_match('/^(\/\/|#|\/\*|\*|<!--)/', $trimmedLine) &&
                !preg_match('/^\[.*?(note|comment|commentaire).*?\]/i', $trimmedLine);
        });

        $response = implode("\n", $cleanedLines);
        $response = preg_replace('/\n{3,}/', "\n\n", $response);
        $response = trim($response);

        return $response;
    }

    /**
     * Méthode utilitaire pour formater les questions pour l'API
     */
    protected function formatQuestionForApi(Question $question): array
    {
        return [
            'id' => $question->id,
            'content' => $question->content,
            'created_at' => $question->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $question->updated_at->format('Y-m-d H:i:s'),
            'domain_id' => $question->domain_id,
            'domain_name' => $question->domain->name ?? 'Domaine supprimé',
            'is_programming' => $question->isProgrammingQuestion(),
            'has_evaluation' => $question->evaluation ? true : false,
            'responses_count' => $question->iaResponses->count(),
            'evaluation_status' => $this->getQuestionEvaluationStatus($question),
            'summary' => Str::limit($question->content, 150),
            'tags' => $this->generateQuestionTags($question),
        ];
    }

    /**
     * Génère des tags pour une question
     */
    protected function generateQuestionTags(Question $question): array
    {
        $tags = [];

        // Tag du domaine
        $tags[] = $question->domain->name ?? 'Sans domaine';

        // Tag de programmation
        if ($question->isProgrammingQuestion()) {
            $tags[] = 'Programmation';

            if ($question->evaluation) {
                $tags[] = 'Évaluée';
            } else {
                $tags[] = 'En attente d\'évaluation';
            }
        }

        // Tag selon le nombre de réponses
        $responseCount = $question->iaResponses->count();
        if ($responseCount >= 3) {
            $tags[] = 'Complète';
        } elseif ($responseCount > 0) {
            $tags[] = 'Partielle';
        } else {
            $tags[] = 'Sans réponse';
        }

        return $tags;
    }

    private function calculateAverageScore($evaluation): float
    {
        $scores = array_filter([
            $evaluation->note_gpt4,
            $evaluation->note_deepseek,
            $evaluation->note_qwen
        ], function($score) {
            return !is_null($score) && is_numeric($score);
        });

        return count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0.0;
    }

    private function parseEvaluationJson($jsonData): ?array
    {
        if (is_null($jsonData)) {
            return null;
        }

        if (is_array($jsonData)) {
            return $jsonData;
        }

        if (is_string($jsonData) && !empty(trim($jsonData))) {
            $decoded = json_decode($jsonData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
