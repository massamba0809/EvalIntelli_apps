<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\Question;
use App\Models\IaResponse;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class QuestionController extends Controller
{
    protected $openRouter;

    public function __construct(OpenRouterService $openRouterService)
    {
        $this->openRouter = $openRouterService;
    }

    /**
     * Affiche le formulaire pour poser une question dans un domaine spécifique
     */
    public function showForm(Domain $domain)
    {
        return view('ia.form', compact('domain'));
    }

    /**
     * Traite la soumission d'une question et retourne les réponses des IA
     */
    public function submit(Request $request, Domain $domain)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:2000'
        ]);

        // Enregistrer la question
        $question = Question::create([
            'user_id' => Auth::id(),
            'domain_id' => $domain->id,
            'content' => $validated['question']
        ]);

        // Préparer le prompt avec le template du domaine
        $prompt = $this->preparePrompt($domain, $validated['question']);

        // Interroger les modèles IA
        $responses = $this->queryAImodels($prompt);

        // Enregistrer les réponses
        $this->saveResponses($question->id, $responses);

        // ===== DIAGNOSTIC DE DÉTECTION AVEC LOGS =====
        $question->load(['domain', 'iaResponses']);

        $isProgramming = $question->isProgrammingQuestion();
        $isMathematical = $question->isMathematicalQuestion();
        $isEvaluable = $question->isEvaluableQuestion();
        $evaluationType = $question->getEvaluationType();

        \Log::info('DIAGNOSTIC POST-SOUMISSION', [
            'question_id' => $question->id,
            'domain_name' => $domain->name,
            'domain_slug' => $domain->slug ?? 'N/A',
            'content_preview' => Str::limit($validated['question'], 100),
            'is_programming' => $isProgramming,
            'is_mathematical' => $isMathematical,
            'is_evaluable' => $isEvaluable,
            'evaluation_type' => $evaluationType,
            'responses_count' => $question->iaResponses->count()
        ]);

        // ===== ÉVALUATION AUTOMATIQUE IMMÉDIATE (SANS CHARGEMENT) =====
        if ($isEvaluable) {
            $this->triggerImmediateEvaluationIfApplicable($question);
        }

        // Récupérer les réponses enregistrées pour affichage
        $iaResponses = $question->iaResponses()->get()->map(function ($response) use ($question) {
            $response->cleaned_response = $this->cleanResponse($response->response);
            $response->is_evaluable = $question->isEvaluableQuestion();
            $response->evaluation_type = $question->getEvaluationType();
            return $response;
        });

        return view('ia.results', [
            'question' => $question,
            'domain' => $domain,
            'responses' => $iaResponses,
            'is_programming' => $isProgramming,
            'is_mathematics' => $isMathematical,
            'is_evaluable' => $isEvaluable,
            'evaluation_type' => $evaluationType
        ]);
    }

    /**
     * Affiche les résultats d'une question spécifique par son ID
     */
    public function showResultsById(Request $request)
    {
        try {
            $questionId = $request->query('question');

            if (!$questionId) {
                return redirect()->route('ia.index')->with('error', 'Question non spécifiée');
            }

            // Récupérer la question
            $question = Question::with(['domain', 'iaResponses', 'evaluation'])
                ->where('id', $questionId)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // ===== DIAGNOSTIC DÉTAILLÉ =====
            $isProgramming = $question->isProgrammingQuestion();
            $isMathematical = $question->isMathematicalQuestion();
            $isEvaluable = $question->isEvaluableQuestion();
            $evaluationType = $question->getEvaluationType();

            \Log::info('DIAGNOSTIC RESULTS BY ID', [
                'question_id' => $question->id,
                'domain_name' => $question->domain->name,
                'is_programming' => $isProgramming,
                'is_mathematical' => $isMathematical,
                'is_evaluable' => $isEvaluable,
                'evaluation_type' => $evaluationType,
                'has_evaluation' => $question->evaluation ? true : false,
                'responses_count' => $question->iaResponses->count()
            ]);

            // ===== VÉRIFICATION ET DÉCLENCHEMENT D'ÉVALUATION SI NÉCESSAIRE =====
            if ($isEvaluable && !$question->evaluation && $question->hasAllAIResponses()) {
                \Log::info('Question évaluable sans évaluation, déclenchement automatique', [
                    'question_id' => $question->id,
                    'type' => $evaluationType
                ]);
                $this->triggerImmediateEvaluationIfApplicable($question);
            }

            // Préparer les réponses pour l'affichage
            $iaResponses = $question->iaResponses->map(function ($response) use ($question) {
                $response->cleaned_response = $this->cleanResponse($response->response);
                $response->is_evaluable = $question->isEvaluableQuestion();
                $response->evaluation_type = $question->getEvaluationType();
                return $response;
            });

            return view('ia.results', [
                'question' => $question,
                'domain' => $question->domain,
                'responses' => $iaResponses,
                'is_programming' => $isProgramming,
                'is_mathematics' => $isMathematical,
                'is_evaluable' => $isEvaluable,
                'evaluation_type' => $evaluationType
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('ia.index')->with('error', 'Question non trouvée ou accès non autorisé');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'affichage des résultats par ID', [
                'question_id' => $request->query('question'),
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('ia.index')->with('error', 'Erreur lors du chargement de la question');
        }
    }

    /**
     * Vérifie si le domaine concerne la programmation
     */
    protected function isProgrammingDomain(Domain $domain): bool
    {
        if (!$domain) return false;

        $programmingKeywords = ['programmation', 'code', 'développement', 'programming', 'coding'];

        return Str::contains(Str::lower($domain->name), $programmingKeywords) ||
            Str::contains(Str::lower($domain->slug ?? ''), $programmingKeywords);
    }

    /**
     * Vérifie si le domaine concerne les mathématiques
     */
    protected function isMathematicsDomain(Domain $domain): bool
    {
        if (!$domain) return false;

        $mathKeywords = ['mathématiques', 'math', 'logique', 'mathematics', 'logic', 'calcul'];

        return Str::contains(Str::lower($domain->name), $mathKeywords) ||
            Str::contains(Str::lower($domain->slug ?? ''), $mathKeywords);
    }

    /**
     * Nettoie la réponse en supprimant les commentaires et formatage indésirable
     */
    protected function cleanResponse(string $response): string
    {
        // Supprimer les commentaires HTML (<!-- commentaire -->)
        $response = preg_replace('/<!--.*?-->/s', '', $response);

        // Supprimer les commentaires de style // ou /* */
        $response = preg_replace('/\/\/.*$/m', '', $response);
        $response = preg_replace('/\/\*.*?\*\//s', '', $response);

        // Supprimer les commentaires markdown (<!-- commentaire -->)
        $response = preg_replace('/\[\s*\/\/.*?\]/s', '', $response);

        // Supprimer les balises de commentaire spécifiques aux IA
        $response = preg_replace('/\[Note:.*?\]/si', '', $response);
        $response = preg_replace('/\[Commentaire:.*?\]/si', '', $response);
        $response = preg_replace('/\[Comment:.*?\]/si', '', $response);

        // Supprimer les lignes qui commencent par des indicateurs de commentaire
        $lines = explode("\n", $response);
        $cleanedLines = array_filter($lines, function($line) {
            $trimmedLine = trim($line);
            return !empty($trimmedLine) &&
                !preg_match('/^(\/\/|#|\/\*|\*|<!--)/', $trimmedLine) &&
                !preg_match('/^\[.*?(note|comment|commentaire).*?\]/i', $trimmedLine);
        });

        // Rejoindre les lignes et nettoyer les espaces multiples
        $response = implode("\n", $cleanedLines);
        $response = preg_replace('/\n{3,}/', "\n\n", $response);
        $response = trim($response);

        return $response;
    }

    /**
     * Prépare le prompt en fonction du domaine - VERSION AMÉLIORÉE
     */
    protected function preparePrompt(Domain $domain, string $question): string
    {
        $basePrompt = $domain->prompt_template ?
            str_replace('{question}', $question, $domain->prompt_template) :
            $question;

        // Adapter le prompt selon le type de domaine DÉTECTÉ
        $domainName = strtolower($domain->name);
        $domainSlug = strtolower($domain->slug ?? '');

        // Détection de domaine programmation
        if (Str::contains($domainName, ['programmation', 'programming', 'code', 'développement']) ||
            Str::contains($domainSlug, ['programming', 'coding'])) {

            \Log::info('Prompt adapté pour PROGRAMMATION', [
                'domain_name' => $domain->name,
                'domain_slug' => $domain->slug ?? 'N/A'
            ]);

            $cleanPrompt = $basePrompt . "\n\nVeuillez répondre de manière directe avec du code fonctionnel et des explications techniques claires. Évitez les commentaires ou annotations entre crochets.";

            // Détection de domaine mathématiques
        } elseif (Str::contains($domainName, ['mathématiques', 'mathematics', 'math', 'logique', 'logic', 'calcul']) ||
            Str::contains($domainSlug, ['math', 'logic'])) {

            \Log::info('Prompt adapté pour MATHÉMATIQUES', [
                'domain_name' => $domain->name,
                'domain_slug' => $domain->slug ?? 'N/A'
            ]);

            $cleanPrompt = $basePrompt . "\n\nVeuillez répondre de manière précise et rigoureuse, en montrant clairement vos calculs et raisonnements mathématiques. Évitez les commentaires ou annotations entre crochets.";

            // Domaine général
        } else {
            \Log::info('Prompt adapté pour domaine GÉNÉRAL', [
                'domain_name' => $domain->name,
                'domain_slug' => $domain->slug ?? 'N/A'
            ]);

            $cleanPrompt = $basePrompt . "\n\nVeuillez répondre de manière claire et concise.";
        }

        return $cleanPrompt;
    }

    /**
     * Interroge les modèles IA via OpenRouter
     */
    protected function queryAImodels(string $prompt): array
    {
        $models = [
            'openai/gpt-4o',
            'deepseek/deepseek-r1',
            'qwen/qwen-2.5-72b-instruct',
        ];

        return $this->openRouter->queryMultipleModelsAsync($models, $prompt);
    }

    /**
     * Enregistre les réponses des IA dans la base de données
     */
    protected function saveResponses(int $questionId, array $apiResponses): void
    {
        foreach ($apiResponses as $model => $response) {
            if ($response['status'] === 'success') {
                $content = $response['response']['choices'][0]['message']['content'] ?? 'No response';
                $tokenUsage = $response['response']['usage']['total_tokens'] ?? null;

                IaResponse::create([
                    'question_id' => $questionId,
                    'model_name' => $model,
                    'response' => $content,
                    'token_usage' => $tokenUsage,
                    'response_time' => $response['response_time'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                IaResponse::create([
                    'question_id' => $questionId,
                    'model_name' => $model,
                    'response' => 'Erreur: ' . ($response['response'] ?? 'Unknown error'),
                    'token_usage' => null,
                    'response_time' => $response['response_time'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                logger()->error('Erreur API OpenRouter', [
                    'model' => $model,
                    'error' => $response['response'] ?? 'Unknown error',
                    'response_time' => $response['response_time'] ?? null,
                    'question_id' => $questionId
                ]);
            }
        }
    }

    /**
     * Déclenche l'évaluation automatique si applicable - VERSION CORRIGÉE
     */
    protected function triggerImmediateEvaluationIfApplicable(Question $question): void
    {
        try {
            $question->load(['domain', 'iaResponses']);

            // Diagnostic complet
            $isProgramming = $question->isProgrammingQuestion();
            $isMathematical = $question->isMathematicalQuestion();
            $isEvaluable = $question->isEvaluableQuestion();
            $evaluationType = $question->getEvaluationType();

            \Log::info('DÉCLENCHEMENT ÉVALUATION AUTOMATIQUE', [
                'question_id' => $question->id,
                'domain_name' => $question->domain->name,
                'is_programming' => $isProgramming,
                'is_mathematical' => $isMathematical,
                'is_evaluable' => $isEvaluable,
                'evaluation_type' => $evaluationType,
                'responses_count' => $question->iaResponses->count()
            ]);

            // Vérifier si la question est évaluable
            if (!$isEvaluable) {
                \Log::info('Question non évaluable, pas d\'évaluation automatique', [
                    'question_id' => $question->id,
                    'domain' => $question->domain->name,
                    'is_programming' => $isProgramming,
                    'is_mathematics' => $isMathematical
                ]);
                return;
            }

            // Vérifier qu'on a bien les 3 réponses
            $responseCount = $question->iaResponses()->count();

            if ($responseCount < 3) {
                \Log::info('Pas assez de réponses pour évaluation automatique', [
                    'question_id' => $question->id,
                    'responses_count' => $responseCount
                ]);
                return;
            }

            // Recharger la question pour s'assurer d'avoir les dernières données
            $question->refresh();
            $question->load(['domain', 'iaResponses']);

            // ===== ÉVALUATION EN ARRIÈRE-PLAN IMMÉDIATE =====
            // Utiliser une queue ou exécuter directement selon votre configuration
            if (config('queue.default') !== 'sync') {
                // Avec queue (recommandé pour la production)
                dispatch(new \App\Jobs\EvaluateQuestionJob($question))->onQueue('evaluations');
                \Log::info('Évaluation automatique mise en queue', [
                    'question_id' => $question->id,
                    'type' => $evaluationType
                ]);
            } else {
                // Exécution immédiate (pour développement/test)
                $this->executeImmediateEvaluation($question);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur évaluation automatique immédiate', [
                'question_id' => $question->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Vérifie si l'utilisateur connecté peut accéder à cette question
     */
    protected function canAccessQuestion(Question $question): bool
    {
        return $question->user_id === Auth::id();
    }

    /**
     * Exécute l'évaluation immédiate en synchrone
     */
    protected function executeImmediateEvaluation(Question $question): void
    {
        try {
            // Lancer l'évaluation via EvaluationController
            $evaluationController = app(\App\Http\Controllers\EvaluationController::class);
            $result = $evaluationController->evaluateQuestion($question);

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                $data = $result->getData(true);
                if ($data['success']) {
                    \Log::info('Évaluation automatique immédiate réussie', [
                        'question_id' => $question->id,
                        'evaluation_id' => $data['evaluation_id'] ?? 'N/A',
                        'evaluation_type' => $data['evaluation_type'] ?? 'unknown',
                        'has_wolfram_reference' => $data['has_wolfram_reference'] ?? false
                    ]);
                } else {
                    \Log::warning('Évaluation automatique immédiate échouée', [
                        'question_id' => $question->id,
                        'message' => $data['message']
                    ]);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Erreur exécution évaluation immédiate', [
                'question_id' => $question->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtient les détails d'une question pour l'API
     */







}
