<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Evaluation;
use App\Services\OpenRouterService;
use App\Services\WolframAlphaService;
use App\Services\DeepLService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    protected $openRouter;
    protected $wolfram;
    protected $deepL;

    public function __construct(
        OpenRouterService $openRouterService,
        WolframAlphaService $wolframService,
        DeepLService $deepLService
    ) {
        $this->openRouter = $openRouterService;
        $this->wolfram = $wolframService;
        $this->deepL = $deepLService;
    }

    /**
     * Affiche l'évaluation d'une question
     */
    public function show(Question $question)
    {
        try {
            // Vérifier que la question appartient à l'utilisateur connecté
            if ($question->user_id !== Auth::id()) {
                return redirect()->route('ia.index')->with('error', 'Accès non autorisé à cette question');
            }

            // IMPORTANT : Charger TOUTES les relations nécessaires
            $question->load(['domain', 'iaResponses', 'evaluation']);

            \Log::info('Affichage page évaluation', [
                'question_id' => $question->id,
                'domain' => $question->domain->name ?? 'N/A',
                'has_evaluation' => $question->evaluation ? true : false,
                'responses_count' => $question->iaResponses->count(),
                'is_programming' => $question->isProgrammingQuestion(),
                'is_mathematical' => $question->isMathematicalQuestion(),
                'is_translation' => $this->isTranslationQuestion($question)
            ]);

            // Vérifier que c'est une question évaluable
            if (!$question->isEvaluableQuestion()) {
                return redirect()->route('ia.results.by.id', ['question' => $question->id])
                    ->with('info', 'Cette question n\'est pas évaluable automatiquement.');
            }

            // Vérifier qu'on a les réponses nécessaires
            if (!$question->hasAllAIResponses()) {
                return redirect()->route('ia.results.by.id', ['question' => $question->id])
                    ->with('warning', 'Toutes les réponses IA ne sont pas encore disponibles.');
            }

            // Déterminer le type d'évaluation AVEC LOG
            $evaluationType = $question->getEvaluationType();

            \Log::info('Type d\'évaluation déterminé', [
                'question_id' => $question->id,
                'evaluation_type' => $evaluationType,
                'domain_name' => $question->domain->name,
                'is_programming' => $question->isProgrammingQuestion(),
                'is_mathematical' => $question->isMathematicalQuestion(),
                'is_translation' => $this->isTranslationQuestion($question)
            ]);

            // Préparer les réponses par modèle avec nettoyage COMPLET
            $responsesByModel = $question->getResponsesByModel();
            $responses = [];

            // CORRECTION : S'assurer qu'on a toutes les réponses dans le bon format
            $expectedModels = ['gpt4', 'deepseek', 'qwen'];
            foreach ($expectedModels as $modelKey) {
                if (isset($responsesByModel[$modelKey])) {
                    $response = $responsesByModel[$modelKey];
                    $response->cleaned_response = $this->cleanResponse($response->response);
                    $responses[$modelKey] = $response;
                } else {
                    \Log::warning('Réponse manquante pour le modèle', [
                        'question_id' => $question->id,
                        'missing_model' => $modelKey,
                        'available_models' => array_keys($responsesByModel)
                    ]);
                }
            }

            // Vérifier qu'on a une évaluation
            $evaluation = $question->evaluation;

            if (!$evaluation) {
                \Log::warning('Pas d\'évaluation trouvée pour la question évaluable', [
                    'question_id' => $question->id,
                    'type' => $evaluationType,
                    'responses_available' => count($responses)
                ]);

                // Essayer de déclencher l'évaluation automatiquement
                try {
                    \Log::info('Tentative de déclenchement automatique de l\'évaluation');
                    $evaluationResult = $this->evaluateQuestion($question);

                    if ($evaluationResult instanceof \Illuminate\Http\JsonResponse) {
                        $data = $evaluationResult->getData(true);
                        if ($data['success']) {
                            $question->refresh();
                            $question->load(['evaluation']);
                            $evaluation = $question->evaluation;
                            \Log::info('Évaluation automatique réussie', ['evaluation_id' => $evaluation->id ?? 'N/A']);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Erreur déclenchement évaluation automatique', [
                        'question_id' => $question->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // VÉRIFICATION FINALE : S'assurer que l'évaluation est complète
            if ($evaluation) {
                \Log::info('Évaluation trouvée', [
                    'evaluation_id' => $evaluation->id,
                    'type' => $evaluation->evaluation_type ?? 'unknown',
                    'notes' => [
                        'gpt4' => $evaluation->note_gpt4,
                        'deepseek' => $evaluation->note_deepseek,
                        'qwen' => $evaluation->note_qwen
                    ],
                    'best_ai' => $evaluation->meilleure_ia,
                    'has_details' => [
                        'gpt4' => !is_null($evaluation->evaluation_gpt4),
                        'deepseek' => !is_null($evaluation->evaluation_deepseek),
                        'qwen' => !is_null($evaluation->evaluation_qwen)
                    ]
                ]);
            }

            return view('ia.evaluation', [
                'question' => $question,
                'evaluation' => $evaluation,
                'responses' => $responses,
                'evaluation_type' => $evaluationType,
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur dans EvaluationController@show', [
                'question_id' => $question->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('ia.index')->with('error', 'Erreur lors du chargement de l\'évaluation : ' . $e->getMessage());
        }
    }



    public function evaluateQuestion(Question $question)
    {
        try {
            // Vérifier que la question appartient à l'utilisateur connecté
            if ($question->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à cette question'
                ], 403);
            }

            // Charger les réponses
            $question->load(['iaResponses', 'domain']);

            // 🎯 DIAGNOSTIC COMPLET AVEC PRIORITÉ ABSOLUE AU DOMAINE CHOISI
            $domainName = $question->domain->name ?? 'Inconnu';
            $domainSlug = $question->domain->slug ?? '';

            // NOUVEAU : Détection basée UNIQUEMENT sur le domaine choisi par l'utilisateur
            $userChosenDomain = $question->domain;
            $finalType = 'none';
            $reason = '';

            if ($userChosenDomain) {
                $domainNameLower = strtolower($domainName);
                $domainSlugLower = strtolower($domainSlug);

                // Priorité absolue au domaine choisi par l'utilisateur
                if (str_contains($domainNameLower, 'traduction') || str_contains($domainSlugLower, 'traduction')) {
                    $finalType = 'translation';
                    $reason = "Domaine choisi par l'utilisateur: '{$domainName}'";

                } elseif (str_contains($domainNameLower, 'math') || str_contains($domainNameLower, 'logique') ||
                    str_contains($domainSlugLower, 'math') || str_contains($domainSlugLower, 'logique')) {
                    $finalType = 'mathematics';
                    $reason = "Domaine choisi par l'utilisateur: '{$domainName}'";

                } elseif (str_contains($domainNameLower, 'programmation') || str_contains($domainNameLower, 'programming') ||
                    str_contains($domainSlugLower, 'programmation') || str_contains($domainSlugLower, 'programming')) {
                    $finalType = 'programming';
                    $reason = "Domaine choisi par l'utilisateur: '{$domainName}'";

                } elseif (str_contains($domainNameLower, 'chimie') || str_contains($domainSlugLower, 'chimie')) {
                    $finalType = 'chemistry';
                    $reason = "Domaine choisi par l'utilisateur: '{$domainName}'";

                } else {
                    $finalType = 'none';
                    $reason = "Domaine '{$domainName}' non évaluable automatiquement";
                }
            }

            \Log::info('🎯 DIAGNOSTIC ÉVALUATION - RESPECT DU DOMAINE UTILISATEUR', [
                'question_id' => $question->id,
                'domain_name' => $domainName,
                'domain_slug' => $domainSlug,
                'forced_type' => $finalType,
                'reason' => $reason,
                'content_preview' => Str::limit($question->content, 100)
            ]);

            // Vérifier que c'est une question évaluable
            if ($finalType === 'none') {
                return response()->json([
                    'success' => false,
                    'message' => "Cette question n'est pas évaluable automatiquement. Domaine: {$domainName}"
                ], 400);
            }

            // Vérifier qu'on a toutes les réponses
            if (!$question->hasAllAIResponses()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pas assez de réponses pour effectuer l\'évaluation (minimum 3 requises)'
                ], 400);
            }

            // Vérifier si une évaluation existe déjà
            $existingEvaluation = Evaluation::where('question_id', $question->id)->first();
            if ($existingEvaluation) {
                return response()->json([
                    'success' => true,
                    'message' => 'Évaluation déjà existante',
                    'evaluation_id' => $existingEvaluation->id,
                    'evaluation_type' => $existingEvaluation->evaluation_type ?? $finalType,
                    'forced_type' => $finalType,
                    'reason' => $reason
                ]);
            }

            // Lancer l'évaluation selon le type forcé par le domaine
            \Log::info('✅ DÉCISION FINALE ÉVALUATION', [
                'question_id' => $question->id,
                'final_type' => $finalType,
                'reason' => $reason,
                'will_use_wolfram' => ($finalType === 'mathematics'),
                'will_use_deepl' => ($finalType === 'translation'),
                'domaine_choisi' => $domainName
            ]);

            // CORRECTION : Utiliser les noms de méthodes corrects
            switch ($finalType) {
                case 'translation':
                    return $this->evaluateTranslationQuestion($question);

                case 'mathematics':
                    // CORRECTION : Utiliser le nom correct de la méthode existante
                    return $this->evaluateMathematicalQuestion($question);

                case 'programming':
                    return $this->evaluateProgrammingQuestion($question);

                case 'chemistry':
                    // CORRECTION : Ne plus utiliser app()->bound() mais directement essayer d'instancier
                    try {
                        // Test direct d'instanciation du service
                        $chimieService = app(\App\Services\ChimieEvaluationService::class);

                        \Log::info('✅ Service ChimieEvaluationService disponible', [
                            'question_id' => $question->id,
                            'service_class' => get_class($chimieService)
                        ]);

                        // Si on arrive ici, le service existe, déléguer au ChimieController
                        $chimieController = app(\App\Http\Controllers\ChimieController::class);
                        return $chimieController->evaluateChemistryQuestion(request(), $question);

                    } catch (\Exception $e) {
                        \Log::error('❌ Service ChimieEvaluationService non disponible - FALLBACK', [
                            'question_id' => $question->id,
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);

                        // FALLBACK : Utiliser l'évaluation de programmation comme alternative
                        \Log::info('🔄 FALLBACK: Évaluation chimie → programmation', [
                            'question_id' => $question->id,
                            'domain_name' => $domainName
                        ]);

                        $result = $this->evaluateProgrammingQuestion($question);

                        // Modifier le type d'évaluation pour indiquer que c'était prévu pour la chimie
                        if ($result instanceof \Illuminate\Http\JsonResponse) {
                            $data = $result->getData(true);
                            if ($data['success'] && isset($data['evaluation_id'])) {
                                // Mettre à jour l'évaluation créée pour indiquer le fallback
                                $evaluation = Evaluation::find($data['evaluation_id']);
                                if ($evaluation) {
                                    $evaluation->update([
                                        'evaluation_type' => 'chemistry_fallback',
                                        'commentaire_global' => 'Évaluation programmation utilisée (service chimie indisponible)'
                                    ]);
                                }
                            }
                        }

                        return $result;
                    }

                default:
                    return response()->json([
                        'success' => false,
                        'message' => "Type d'évaluation non supporté: {$finalType}"
                    ], 400);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'évaluation', [
                'question_id' => $question->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'évaluation: ' . $e->getMessage()
            ], 500);
        }
    }



    protected function evaluateChemistryQuestion(Question $question)
    {
        try {
            Log::info('🧪 ÉVALUATION CHIMIE DÉMARRÉE', [
                'question_id' => $question->id,
                'domain' => $question->domain->name ?? 'N/A'
            ]);

            // Utiliser le service chimie
            $chimieService = app(\App\Services\ChimieEvaluationService::class);
            $result = $chimieService->evaluateChemistryQuestion($question);

            if ($result['success']) {
                $evaluation = $result['evaluation'];

                return response()->json([
                    'success' => true,
                    'message' => 'Évaluation chimie générée avec succès',
                    'evaluation_id' => $evaluation->id,
                    'evaluation_type' => 'chemistry',
                    'has_wolfram_reference' => !is_null($evaluation->wolfram_reference),
                    'evaluation' => [
                        'note_gpt4' => $evaluation->note_gpt4,
                        'note_deepseek' => $evaluation->note_deepseek,
                        'note_qwen' => $evaluation->note_qwen,
                        'meilleure_ia' => $evaluation->meilleure_ia,
                        'commentaire_global' => $evaluation->commentaire_global,
                    ]
                ]);
            } else {
                throw new \Exception('Échec de l\'évaluation chimie');
            }

        } catch (\Exception $e) {
            Log::error('❌ ERREUR ÉVALUATION CHIMIE', [
                'question_id' => $question->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'évaluation chimie: ' . $e->getMessage()
            ], 500);
        }
    }
    protected function isTranslationQuestion(Question $question): bool
    {
        if (!$question->domain) {
            return false;
        }

        // 🎯 PRIORITÉ 1 : Vérification STRICTE par nom de domaine
        $domainName = strtolower($question->domain->name);
        $domainSlug = strtolower($question->domain->slug ?? '');

        // Mots-clés EXPLICITES pour traduction
        $translationDomains = [
            'traduction', 'translation', 'translate', 'traduire',
            'langues', 'languages', 'linguistique', 'linguistics'
        ];

        // Si le domaine contient ces mots, c'est DÉFINITIVEMENT de la traduction
        foreach ($translationDomains as $keyword) {
            if (str_contains($domainName, $keyword) || str_contains($domainSlug, $keyword)) {
                \Log::info('Question TRADUCTION détectée par domaine explicite', [
                    'question_id' => $question->id,
                    'domain_name' => $question->domain->name,
                    'keyword_matched' => $keyword
                ]);
                return true;
            }
        }

        // 🎯 PRIORITÉ 2 : Analyse du contenu seulement si le domaine est ambigu
        $hasTranslationContent = $this->hasTranslationContent($question->content);

        if ($hasTranslationContent) {
            \Log::info('Question TRADUCTION détectée par contenu', [
                'question_id' => $question->id,
                'domain_name' => $question->domain->name,
                'content_preview' => Str::limit($question->content, 100)
            ]);
        }

        return $hasTranslationContent;
    }

    /**
     * NOUVELLE MÉTHODE : Détection spécifique du contenu de traduction
     */
    protected function hasTranslationContent(string $content): bool
    {
        $translationKeywords = [
            // Mots-clés français
            'traduire', 'traduisez', 'traduction', 'traduis',
            'en français', 'en anglais', 'en espagnol', 'en allemand',
            'vers le français', 'vers l\'anglais',

            // Mots-clés anglais
            'translate', 'translation', 'translate to',
            'into french', 'into english', 'into spanish',
            'from french', 'from english',

            // Patterns de langues
            'français-anglais', 'anglais-français',
            'french-english', 'english-french',
            'spanish-english', 'german-french',

            // Expressions courantes
            'comment dit-on', 'comment dire',
            'what is', 'in french', 'in english',
            'que signifie', 'que veut dire'
        ];

        $contentLower = strtolower($content);

        // Compter les mots-clés de traduction trouvés
        $translationMatches = 0;
        foreach ($translationKeywords as $keyword) {
            if (str_contains($contentLower, $keyword)) {
                $translationMatches++;
            }
        }

        // Vérifier les patterns de traduction EXPLICITES
        $translationPatterns = [
            '/traduire?\s*[:]\s*.+/i',                  // "Traduire: Hello"
            '/translate\s+to\s+\w+\s*[:]\s*.+/i',      // "Translate to French: Hello"
            '/en\s+\w+\s*[:]\s*.+/i',                  // "En français: Hello"
            '/\w+\s+to\s+\w+\s*[:]\s*.+/i',           // "English to French: Hello"
            '/comment\s+dit-on\s*.+/i',                // "Comment dit-on..."
            '/que\s+signifie\s*.+/i',                  // "Que signifie..."
        ];

        $translationPatternMatches = 0;
        foreach ($translationPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $translationPatternMatches++;
            }
        }

        // C'est de la traduction si :
        // - Au moins 1 mot-clé de traduction OU
        // - Au moins 1 pattern de traduction clair
        $isTranslation = ($translationMatches >= 1) || ($translationPatternMatches >= 1);

        if ($isTranslation) {
            \Log::info('Contenu traduction détecté', [
                'translation_matches' => $translationMatches,
                'pattern_matches' => $translationPatternMatches,
                'content_preview' => Str::limit($content, 100)
            ]);
        }

        return $isTranslation;
    }

    /**
     * NOUVELLE MÉTHODE : Évaluation spécifique pour les questions de traduction avec DeepL
     */
    protected function evaluateTranslationQuestion(Question $question)
    {
        try {
            \Log::info('🌐 DÉBUT ÉVALUATION TRADUCTION AVEC DEEPL', [
                'question_id' => $question->id,
                'domain' => $question->domain->name,
                'content' => $question->content
            ]);

            // 🎯 ÉTAPE 1 : Analyser la question pour extraire langues source/cible
            $translationData = $this->parseTranslationRequest($question->content);

            \Log::info('📋 Données de traduction parsées', $translationData);

            if (!$translationData['is_valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format de question de traduction invalide: ' . ($translationData['error'] ?? 'Unknown error')
                ], 400);
            }

            // 🎯 ÉTAPE 2 : DeepL traduction de référence AVEC DEBUG COMPLET
            \Log::info('🚀 DÉBUT APPEL DEEPL DANS ÉVALUATION', [
                'question_id' => $question->id,
                'translation_data' => $translationData,
                'deepl_service_class' => get_class($this->deepL),
                'deepl_config' => $this->deepL->validateConfiguration()
            ]);

            $deepLResult = null;
            $deepLReference = null;
            $deepLStatus = 'failed';
            $deepLResponseTime = null;

            try {
                // 🔍 LOG DES PARAMÈTRES EXACTS
                \Log::info('📋 Paramètres pour DeepL', [
                    'source_text' => $translationData['source_text'],
                    'source_text_length' => strlen($translationData['source_text']),
                    'target_language' => $translationData['target_language'],
                    'source_language' => $translationData['source_language'],
                    'pattern_matched' => $translationData['pattern_matched'] ?? 'unknown'
                ]);

                // 🚀 APPEL DEEPL AVEC GESTION D'ERREURS RENFORCÉE
                $deepLResult = $this->deepL->translate(
                    $translationData['source_text'],
                    $translationData['target_language'],
                    $translationData['source_language']
                );

                \Log::info('📥 RÉPONSE DEEPL COMPLÈTE DANS ÉVALUATION', [
                    'question_id' => $question->id,
                    'result_status' => $deepLResult['status'] ?? 'unknown',
                    'result_keys' => array_keys($deepLResult),
                    'full_result' => $deepLResult,
                    'has_translated_text' => isset($deepLResult['translated_text']),
                    'response_time' => $deepLResult['response_time'] ?? null
                ]);

                if ($deepLResult['status'] === 'success') {
                    $deepLReference = $deepLResult['translated_text'];
                    $deepLStatus = 'success';
                    $deepLResponseTime = $deepLResult['response_time'] ?? null;

                    \Log::info('✅ RÉFÉRENCE DEEPL OBTENUE DANS ÉVALUATION', [
                        'question_id' => $question->id,
                        'source_text' => $translationData['source_text'],
                        'deepl_reference' => $deepLReference,
                        'detected_lang' => $deepLResult['detected_source_language'] ?? 'N/A',
                        'reference_length' => strlen($deepLReference),
                        'deepl_time' => $deepLResponseTime,
                        'key_type' => $deepLResult['key_type'] ?? 'unknown'
                    ]);
                } else {
                    $deepLStatus = 'error';
                    \Log::error('⚠️ DEEPL ÉCHEC DANS ÉVALUATION', [
                        'question_id' => $question->id,
                        'deepl_status' => $deepLResult['status'],
                        'error' => $deepLResult['error'] ?? 'Unknown error',
                        'suggestion' => $deepLResult['suggestion'] ?? 'No suggestion',
                        'full_response' => $deepLResult,
                        'source_text' => $translationData['source_text'],
                        'target_lang' => $translationData['target_language'],
                        'api_url' => $deepLResult['api_url'] ?? 'unknown'
                    ]);
                }

            } catch (\Exception $e) {
                $deepLStatus = 'exception';
                \Log::error('❌ EXCEPTION DEEPL DANS ÉVALUATION', [
                    'question_id' => $question->id,
                    'exception_message' => $e->getMessage(),
                    'exception_file' => $e->getFile(),
                    'exception_line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'source_text' => $translationData['source_text'],
                    'translation_data' => $translationData
                ]);
            }

            // 🎯 DÉCISION : Continuer même sans DeepL
            \Log::info('📊 ÉTAT DEEPL FINAL', [
                'question_id' => $question->id,
                'deepl_status' => $deepLStatus,
                'has_reference' => !is_null($deepLReference),
                'will_continue' => true,
                'reference_preview' => $deepLReference ? substr($deepLReference, 0, 50) : 'null'
            ]);

            // 🎯 ÉTAPE 3 : Récupérer les traductions des IA
            $responsesByModel = $question->getResponsesByModel();
            $translations = [];

            foreach ($responsesByModel as $modelKey => $response) {
                $translations[$modelKey] = $this->cleanResponse($response->response);
            }

            \Log::info('📝 Traductions IA récupérées', [
                'gpt4' => substr($translations['gpt4'] ?? 'MANQUANTE', 0, 100),
                'deepseek' => substr($translations['deepseek'] ?? 'MANQUANTE', 0, 100),
                'qwen' => substr($translations['qwen'] ?? 'MANQUANTE', 0, 100)
            ]);

            // Vérifier qu'on a les 3 traductions nécessaires
            $requiredModels = ['gpt4', 'deepseek', 'qwen'];
            foreach ($requiredModels as $model) {
                if (!isset($translations[$model])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Traduction manquante pour le modèle $model"
                    ], 400);
                }
            }

            // 🎯 ÉTAPE 4 : Générer le prompt d'évaluation de traduction
            $evaluationPrompt = $this->generateTranslationEvaluationPrompt(
                $translationData,
                $translations,
                $deepLReference  // Peut être null
            );

            \Log::info('💬 Prompt généré pour Claude', [
                'prompt_length' => strlen($evaluationPrompt),
                'has_deepl_ref' => !is_null($deepLReference),
                'deepl_section_present' => str_contains($evaluationPrompt, 'RÉFÉRENCE DEEPL')
            ]);

            // 🎯 ÉTAPE 5 : Interroger Claude pour l'évaluation traduction
            $evaluationResponse = $this->openRouter->queryModel('anthropic/claude-3.5-sonnet', $evaluationPrompt);

            if ($evaluationResponse['status'] !== 'success') {
                throw new \Exception('Erreur lors de l\'évaluation par Claude: ' . ($evaluationResponse['response'] ?? 'Unknown error'));
            }

            // 🎯 ÉTAPE 6 : Parser la réponse d'évaluation
            $evaluationContent = $evaluationResponse['response']['choices'][0]['message']['content'] ?? '';
            $parsedEvaluation = $this->parseTranslationEvaluationResponse($evaluationContent);

            \Log::info('📊 Évaluation parsée', [
                'question_id' => $question->id,
                'parsed_keys' => array_keys($parsedEvaluation),
                'notes' => [
                    'gpt4' => $parsedEvaluation['note_gpt4'] ?? 'N/A',
                    'deepseek' => $parsedEvaluation['note_deepseek'] ?? 'N/A',
                    'qwen' => $parsedEvaluation['note_qwen'] ?? 'N/A'
                ],
                'best_ai' => $parsedEvaluation['meilleure_ia'] ?? 'N/A'
            ]);

            // 🎯 ÉTAPE 7 : Créer l'évaluation en base avec type traduction
            $evaluation = Evaluation::create([
                'question_id' => $question->id,
                'evaluation_type' => 'translation',
                'evaluation_gpt4' => $parsedEvaluation['details_gpt4'] ?? null,
                'evaluation_deepseek' => $parsedEvaluation['details_deepseek'] ?? null,
                'evaluation_qwen' => $parsedEvaluation['details_qwen'] ?? null,
                'note_gpt4' => $parsedEvaluation['note_gpt4'] ?? null,
                'note_deepseek' => $parsedEvaluation['note_deepseek'] ?? null,
                'note_qwen' => $parsedEvaluation['note_qwen'] ?? null,
                'meilleure_ia' => $parsedEvaluation['meilleure_ia'] ?? null,
                'commentaire_global' => $parsedEvaluation['commentaire_global'] ?? 'Évaluation de traduction générée automatiquement',
                'token_usage_evaluation' => $evaluationResponse['response']['usage']['total_tokens'] ?? null,
                'response_time_evaluation' => $evaluationResponse['response_time'] ?? null,
                // 🎯 CHAMPS SPÉCIFIQUES À LA TRADUCTION
                'deepl_reference' => $deepLReference, // Peut être null
                'deepl_response_time' => $deepLResponseTime,
                'deepl_status' => $deepLStatus,
                'translation_data' => json_encode([
                    'source_text' => $translationData['source_text'],
                    'source_language' => $translationData['source_language'],
                    'target_language' => $translationData['target_language'],
                    'detected_language' => $deepLResult['detected_source_language'] ?? null,
                    'deepl_error' => $deepLResult['error'] ?? null,
                    'deepl_api_url' => $deepLResult['api_url'] ?? null,
                    'deepl_key_type' => $deepLResult['key_type'] ?? null
                ])
            ]);

            \Log::info('✅ ÉVALUATION TRADUCTION CRÉÉE', [
                'question_id' => $question->id,
                'evaluation_id' => $evaluation->id,
                'has_deepl_ref' => !is_null($deepLReference),
                'deepl_status' => $deepLStatus,
                'type_saved' => $evaluation->evaluation_type,
                'deepl_reference_preview' => $deepLReference ? substr($deepLReference, 0, 50) : 'null'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Évaluation de traduction générée avec succès',
                'evaluation_id' => $evaluation->id,
                'evaluation_type' => 'translation',
                'has_deepl_reference' => !is_null($deepLReference),
                'deepl_status' => $deepLStatus,
                'deepl_error' => $deepLResult['error'] ?? null,
                'translation_data' => $translationData,
                'evaluation' => [
                    'note_gpt4' => $evaluation->note_gpt4,
                    'note_deepseek' => $evaluation->note_deepseek,
                    'note_qwen' => $evaluation->note_qwen,
                    'meilleure_ia' => $evaluation->meilleure_ia,
                    'commentaire_global' => $evaluation->commentaire_global,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ ERREUR ÉVALUATION TRADUCTION', [
                'question_id' => $question->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'évaluation de traduction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * NOUVELLE MÉTHODE : Parse une demande de traduction pour extraire les langues et le texte
     */protected function parseTranslationRequest(string $content): array
{
    $content = trim($content);

    // Patterns SIMPLES et SÛRS pour les demandes de traduction
    $patterns = [
        // "Traduisez en français : Hello world"
        '/traduise?[zs]?\s+en\s+([a-z\s]+)\s*:\s*(.+)/i',

        // "Translate to English: Bonjour le monde"
        '/translate\s+to\s+([a-z\s]+)\s*:\s*(.+)/i',

        // "Traduction français-anglais : Bonjour"
        '/traduction\s+([a-z]+)-([a-z]+)\s*:\s*(.+)/i',

        // "French to English: Bonjour"
        '/([a-z]+)\s+to\s+([a-z]+)\s*:\s*(.+)/i',

        // Format simple "Traduire : Hello world"
        '/traduire?\s*:\s*(.+)/i'
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content, $matches)) {
            switch (count($matches)) {
                case 2: // Format simple "Traduire : texte"
                    return [
                        'is_valid' => true,
                        'source_text' => trim($matches[1]),
                        'source_language' => 'auto',
                        'target_language' => 'FR', // Par défaut français
                        'pattern_matched' => 'simple'
                    ];

                case 3: // "Traduisez en français : texte" ou "Translate to English : texte"
                    $targetLang = $this->normalizeLanguageForDeepL(trim($matches[1]));
                    return [
                        'is_valid' => true,
                        'source_text' => trim($matches[2]),
                        'source_language' => 'auto',
                        'target_language' => $targetLang,
                        'pattern_matched' => 'target_specified'
                    ];

                case 4: // "French to English : texte" ou "français-anglais : texte"
                    $sourceLang = $this->normalizeLanguageForDeepL(trim($matches[1]));
                    $targetLang = $this->normalizeLanguageForDeepL(trim($matches[2]));
                    return [
                        'is_valid' => true,
                        'source_text' => trim($matches[3]),
                        'source_language' => $sourceLang,
                        'target_language' => $targetLang,
                        'pattern_matched' => 'both_languages'
                    ];
            }
        }
    }

    // Si aucun pattern ne correspond, tenter la détection automatique
    if (strlen($content) > 10) {
        return [
            'is_valid' => true,
            'source_text' => $content,
            'source_language' => 'auto',
            'target_language' => 'FR', // Par défaut
            'pattern_matched' => 'auto_fallback'
        ];
    }

    return [
        'is_valid' => false,
        'error' => 'Format de question de traduction non reconnu'
    ];
}

    /**
     * NOUVELLE MÉTHODE : Normalise les noms de langues pour DeepL
     */
    protected function normalizeLanguageForDeepL(string $language): string
    {
        $mappings = [
            'français' => 'FR',
            'french' => 'FR',
            'anglais' => 'EN',
            'english' => 'EN',
            'espagnol' => 'ES',
            'spanish' => 'ES',
            'allemand' => 'DE',
            'german' => 'DE',
            'italien' => 'IT',
            'italian' => 'IT',
            'portugais' => 'PT',
            'portuguese' => 'PT',
            'néerlandais' => 'NL',
            'dutch' => 'NL',
            'polonais' => 'PL',
            'polish' => 'PL',
            'russe' => 'RU',
            'russian' => 'RU',
            'chinois' => 'ZH',
            'chinese' => 'ZH',
            'japonais' => 'JA',
            'japanese' => 'JA'
        ];

        $normalized = strtolower(trim($language));

        if (isset($mappings[$normalized])) {
            return $mappings[$normalized];
        }

        // Si déjà en format ISO, retourner tel quel
        $upper = strtoupper($language);
        if (strlen($upper) === 2 && ctype_alpha($upper)) {
            return $upper;
        }

        return 'FR'; // Fallback
    }

    /**
     * NOUVELLE MÉTHODE : Génère le prompt d'évaluation pour les traductions
     */
    protected function generateTranslationEvaluationPrompt(array $translationData, array $translations, ?string $deepLReference): string
    {
        $deepLSection = '';
        if ($deepLReference) {
            $deepLSection = "\n**TRADUCTION DE RÉFÉRENCE DEEPL :**\n{$deepLReference}\n";
        } else {
            $deepLSection = "\n**TRADUCTION DE RÉFÉRENCE DEEPL :** Non disponible (évaluation basée sur la qualité linguistique intrinsèque)\n";
        }

        $sourceInfo = "**TEXTE SOURCE ({$translationData['source_language']} → {$translationData['target_language']}) :**\n{$translationData['source_text']}\n";

        return "Tu es un expert en évaluation de traductions et en linguistique comparative.

Analyse la demande de traduction suivante et les 3 traductions fournies par différentes IA :

{$sourceInfo}
{$deepLSection}
**TRADUCTION GPT-4 :**
{$translations['gpt4']}

**TRADUCTION DEEPSEEK :**
{$translations['deepseek']}

**TRADUCTION QWEN :**
{$translations['qwen']}

**INSTRUCTIONS D'ÉVALUATION TRADUCTION :**
Évalue chaque traduction selon ces 5 critères. IMPORTANT : Donne une note sur 2 points pour chaque critère ET une analyse textuelle :

1. **Fidélité au sens** (2 points) : La traduction conserve-t-elle le sens exact du texte original ?
2. **Qualité linguistique** (2 points) : Syntaxe, grammaire, ponctuation, fluidité dans la langue cible
3. **Style et ton** (2 points) : Le ton/formalisme du texte est-il adapté au contexte ?
4. **Précision contextuelle** (2 points) : Les mots ou expressions ambigus ont-ils été bien traduits ?
5. **Absence d'hallucination** (2 points) : L'IA a-t-elle inventé des parties ou oublié des éléments ?

**FORMAT DE RÉPONSE OBLIGATOIRE :**
```json
{
  \"comparaison\": {
    \"gpt-4\": {
      \"fidelite_note\": 2,
      \"fidelite\": \"Analyse de la fidélité au sens original...\",
      \"qualite_linguistique_note\": 2,
      \"qualite_linguistique\": \"Évaluation de la syntaxe et grammaire...\",
      \"style_note\": 1,
      \"style\": \"Analyse du style et du ton...\",
      \"precision_contextuelle_note\": 2,
      \"precision_contextuelle\": \"Évaluation du contexte et nuances...\",
      \"hallucination_note\": 2,
      \"hallucination\": \"Vérification des ajouts/omissions...\",
      \"note_sur_10\": 9.0,
      \"commentaire\": \"Analyse détaillée des forces et faiblesses\"
    },
    \"deepseek\": {
      \"fidelite_note\": 1,
      \"fidelite\": \"...\",
      \"qualite_linguistique_note\": 2,
      \"qualite_linguistique\": \"...\",
      \"style_note\": 1,
      \"style\": \"...\",
      \"precision_contextuelle_note\": 1,
      \"precision_contextuelle\": \"...\",
      \"hallucination_note\": 2,
      \"hallucination\": \"...\",
      \"note_sur_10\": 7.0,
      \"commentaire\": \"Analyse détaillée des forces et faiblesses\"
    },
    \"qwen\": {
      \"fidelite_note\": 0,
      \"fidelite\": \"...\",
      \"qualite_linguistique_note\": 1,
      \"qualite_linguistique\": \"...\",
      \"style_note\": 2,
      \"style\": \"...\",
      \"precision_contextuelle_note\": 1,
      \"precision_contextuelle\": \"...\",
      \"hallucination_note\": 1,
      \"hallucination\": \"...\",
      \"note_sur_10\": 5.0,
      \"commentaire\": \"Analyse détaillée des forces et faiblesses\"
    }
  },
  \"meilleure_ia\": \"gpt-4\",
  \"commentaire_global\": \"Comparaison globale des 3 traductions et justification du choix\"
}
```

RÈGLES IMPORTANTES :
- Chaque critère_note doit être un nombre entre 0 et 2
- La note_sur_10 est calculée comme : (somme des 5 notes sur 2) * 10 / 10
- Sois strict et objectif dans l'évaluation
- Si DeepL n'est pas disponible, évalue selon la qualité linguistique intrinsèque
- Privilégie les traductions qui respectent le sens ET la fluidité

IMPORTANT : Réponds UNIQUEMENT avec le JSON valide, sans texte supplémentaire.";
    }

    /**
     * NOUVELLE MÉTHODE : Parse la réponse d'évaluation de traduction de Claude
     */
    protected function parseTranslationEvaluationResponse(string $response): array
    {
        try {
            // Nettoyer la réponse pour extraire le JSON
            $response = trim($response);

            // Rechercher le JSON entre ```json et ```
            if (preg_match('/```json\s*(.*?)\s*```/s', $response, $matches)) {
                $jsonString = $matches[1];
            } else {
                // Essayer de trouver le JSON directement
                $jsonString = $response;
            }

            // Decoder le JSON
            $parsed = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Erreur de parsing JSON: ' . json_last_error_msg());
            }

            // Fonction helper pour extraire les détails avec notes par critère (traduction)
            $extractTranslationDetails = function($aiData) {
                if (!is_array($aiData)) return null;

                return [
                    // Notes numériques par critère (0-2 points chacun)
                    'fidelite' => $this->validateCriterionNote($aiData['fidelite_note'] ?? 0),
                    'qualite_linguistique' => $this->validateCriterionNote($aiData['qualite_linguistique_note'] ?? 0),
                    'style' => $this->validateCriterionNote($aiData['style_note'] ?? 0),
                    'precision_contextuelle' => $this->validateCriterionNote($aiData['precision_contextuelle_note'] ?? 0),
                    'hallucination' => $this->validateCriterionNote($aiData['hallucination_note'] ?? 0),

                    // Analyses textuelles
                    'fidelite_analyse' => trim($aiData['fidelite'] ?? ''),
                    'qualite_linguistique_analyse' => trim($aiData['qualite_linguistique'] ?? ''),
                    'style_analyse' => trim($aiData['style'] ?? ''),
                    'precision_contextuelle_analyse' => trim($aiData['precision_contextuelle'] ?? ''),
                    'hallucination_analyse' => trim($aiData['hallucination'] ?? ''),

                    // Note globale et commentaire
                    'note_totale' => $this->validateNote($aiData['note_sur_10'] ?? 0),
                    'commentaire' => trim($aiData['commentaire'] ?? ''),
                ];
            };

            // Extraire et valider les données
            $result = [
                'note_gpt4' => isset($parsed['comparaison']['gpt-4']['note_sur_10']) ?
                    $this->validateNote($parsed['comparaison']['gpt-4']['note_sur_10']) : null,
                'note_deepseek' => isset($parsed['comparaison']['deepseek']['note_sur_10']) ?
                    $this->validateNote($parsed['comparaison']['deepseek']['note_sur_10']) : null,
                'note_qwen' => isset($parsed['comparaison']['qwen']['note_sur_10']) ?
                    $this->validateNote($parsed['comparaison']['qwen']['note_sur_10']) : null,
                'meilleure_ia' => $this->validateBestAI($parsed['meilleure_ia'] ?? null),
                'commentaire_global' => trim($parsed['commentaire_global'] ?? 'Évaluation de traduction générée automatiquement'),

                // Détails complets avec notes par critère
                'details_gpt4' => $extractTranslationDetails($parsed['comparaison']['gpt-4'] ?? null),
                'details_deepseek' => $extractTranslationDetails($parsed['comparaison']['deepseek'] ?? null),
                'details_qwen' => $extractTranslationDetails($parsed['comparaison']['qwen'] ?? null),
            ];

            \Log::info('Évaluation de traduction parsée avec succès', [
                'notes_globales' => [
                    'gpt4' => $result['note_gpt4'],
                    'deepseek' => $result['note_deepseek'],
                    'qwen' => $result['note_qwen']
                ],
                'details_disponibles' => [
                    'gpt4' => !is_null($result['details_gpt4']),
                    'deepseek' => !is_null($result['details_deepseek']),
                    'qwen' => !is_null($result['details_qwen'])
                ]
            ]);

            return $result;

        } catch (\Exception $e) {
            \Log::warning('Erreur de parsing de l\'évaluation de traduction', [
                'response' => $response,
                'error' => $e->getMessage()
            ]);

            // Fallback avec des valeurs par défaut
            return [
                'note_gpt4' => 7,
                'note_deepseek' => 7,
                'note_qwen' => 7,
                'meilleure_ia' => 'gpt4',
                'commentaire_global' => 'Évaluation de traduction générée automatiquement - erreur de parsing de la réponse originale.',
                'details_gpt4' => null,
                'details_deepseek' => null,
                'details_qwen' => null,
            ];
        }
    }

    /**
     * Évaluation spécifique pour les questions mathématiques avec Wolfram Alpha
     */
    protected function evaluateMathematicalQuestion(Question $question)
    {
        try {
            \Log::info('🧮 DÉBUT ÉVALUATION MATHÉMATIQUE AVEC WOLFRAM', [
                'question_id' => $question->id,
                'domain' => $question->domain->name
            ]);

            // 🎯 ÉTAPE 1 : Wolfram Alpha OBLIGATOIRE pour les mathématiques
            $wolframResponse = $this->wolfram->querySimple($question->content);
            $wolframReference = null;

            if ($wolframResponse['status'] === 'success' && $wolframResponse['has_reference']) {
                $wolframReference = $this->wolfram->formatResponseForEvaluation($wolframResponse['response']);
                \Log::info('✅ Référence Wolfram Alpha obtenue', [
                    'question_id' => $question->id,
                    'reference_length' => strlen($wolframReference),
                    'wolfram_time' => $wolframResponse['response_time'] ?? 'N/A'
                ]);
            } else {
                \Log::warning('⚠️ Wolfram Alpha indisponible pour question mathématique', [
                    'question_id' => $question->id,
                    'wolfram_status' => $wolframResponse['status'],
                    'domain' => $question->domain->name
                ]);
                // Continuer sans référence Wolfram pour les mathématiques
            }

            // 🎯 ÉTAPE 2 : Récupérer les réponses des IA
            $responsesByModel = $question->getResponsesByModel();
            $responses = [];

            foreach ($responsesByModel as $modelKey => $response) {
                $responses[$modelKey] = $this->cleanResponse($response->response);
            }

            // Vérifier qu'on a les 3 réponses nécessaires
            $requiredModels = ['gpt4', 'deepseek', 'qwen'];
            foreach ($requiredModels as $model) {
                if (!isset($responses[$model])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Réponse manquante pour le modèle $model"
                    ], 400);
                }
            }

            // 🎯 ÉTAPE 3 : Générer le prompt d'évaluation mathématique
            $evaluationPrompt = $this->generateMathEvaluationPrompt(
                $question->content,
                $responses,
                $wolframReference
            );

            // 🎯 ÉTAPE 4 : Interroger Claude pour l'évaluation mathématique
            $evaluationResponse = $this->openRouter->queryModel('anthropic/claude-3.5-sonnet', $evaluationPrompt);

            if ($evaluationResponse['status'] !== 'success') {
                throw new \Exception('Erreur lors de l\'évaluation par Claude: ' . ($evaluationResponse['response'] ?? 'Unknown error'));
            }

            // 🎯 ÉTAPE 5 : Parser la réponse d'évaluation
            $evaluationContent = $evaluationResponse['response']['choices'][0]['message']['content'] ?? '';
            $parsedEvaluation = $this->parseMathEvaluationResponse($evaluationContent);

            // 🎯 ÉTAPE 6 : Créer l'évaluation en base avec type mathématique
            $evaluation = Evaluation::create([
                'question_id' => $question->id,
                'evaluation_type' => 'mathematics', // 🎯 TYPE EXPLICITE
                'evaluation_gpt4' => $parsedEvaluation['details_gpt4'] ?? null,
                'evaluation_deepseek' => $parsedEvaluation['details_deepseek'] ?? null,
                'evaluation_qwen' => $parsedEvaluation['details_qwen'] ?? null,
                'note_gpt4' => $parsedEvaluation['note_gpt4'] ?? null,
                'note_deepseek' => $parsedEvaluation['note_deepseek'] ?? null,
                'note_qwen' => $parsedEvaluation['note_qwen'] ?? null,
                'meilleure_ia' => $parsedEvaluation['meilleure_ia'] ?? null,
                'commentaire_global' => $parsedEvaluation['commentaire_global'] ?? 'Évaluation mathématique générée automatiquement',
                'token_usage_evaluation' => $evaluationResponse['response']['usage']['total_tokens'] ?? null,
                'response_time_evaluation' => $evaluationResponse['response_time'] ?? null,
                'wolfram_reference' => $wolframReference,
                'wolfram_response_time' => $wolframResponse['response_time'] ?? null,
                'wolfram_status' => $wolframResponse['status'] ?? 'unknown'
            ]);

            \Log::info('✅ ÉVALUATION MATHÉMATIQUE CRÉÉE', [
                'question_id' => $question->id,
                'evaluation_id' => $evaluation->id,
                'has_wolfram_ref' => !is_null($wolframReference),
                'type_saved' => $evaluation->evaluation_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Évaluation mathématique générée avec succès',
                'evaluation_id' => $evaluation->id,
                'evaluation_type' => 'mathematics',
                'has_wolfram_reference' => !is_null($wolframReference),
                'evaluation' => [
                    'note_gpt4' => $evaluation->note_gpt4,
                    'note_deepseek' => $evaluation->note_deepseek,
                    'note_qwen' => $evaluation->note_qwen,
                    'meilleure_ia' => $evaluation->meilleure_ia,
                    'commentaire_global' => $evaluation->commentaire_global,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ ERREUR ÉVALUATION MATHÉMATIQUE', [
                'question_id' => $question->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'évaluation mathématique: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Évaluation pour les questions de programmation (SANS Wolfram Alpha)
     */
    protected function evaluateProgrammingQuestion(Question $question)
    {
        try {
            \Log::info('💻 DÉBUT ÉVALUATION PROGRAMMATION SANS WOLFRAM', [
                'question_id' => $question->id,
                'domain' => $question->domain->name
            ]);

            // 🎯 ÉTAPE 1 : Récupérer les réponses par modèle (pas de Wolfram)
            $responsesByModel = $question->getResponsesByModel();
            $responses = [];

            foreach ($responsesByModel as $modelKey => $response) {
                $responses[$modelKey] = $this->cleanResponse($response->response);
            }

            // Vérifier qu'on a les 3 réponses nécessaires
            $requiredModels = ['gpt4', 'deepseek', 'qwen'];
            foreach ($requiredModels as $model) {
                if (!isset($responses[$model])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Réponse manquante pour le modèle $model"
                    ], 400);
                }
            }

            // 🎯 ÉTAPE 2 : Générer le prompt d'évaluation PROGRAMMATION (sans Wolfram)
            $evaluationPrompt = $this->generateProgrammingEvaluationPrompt($question->content, $responses);

            // 🎯 ÉTAPE 3 : Interroger GPT-4 pour l'évaluation (pas Claude pour éviter la confusion)
            $evaluationResponse = $this->openRouter->queryModel('openai/gpt-4o', $evaluationPrompt);

            if ($evaluationResponse['status'] !== 'success') {
                throw new \Exception('Erreur lors de l\'évaluation par GPT-4: ' . ($evaluationResponse['response'] ?? 'Unknown error'));
            }

            // 🎯 ÉTAPE 4 : Parser la réponse d'évaluation
            $evaluationContent = $evaluationResponse['response']['choices'][0]['message']['content'] ?? '';
            $parsedEvaluation = $this->parseEvaluationResponse($evaluationContent);

            // 🎯 ÉTAPE 5 : Créer l'évaluation en base avec type programmation
            $evaluation = Evaluation::create([
                'question_id' => $question->id,
                'evaluation_type' => 'programming', // 🎯 TYPE EXPLICITE
                'evaluation_gpt4' => $parsedEvaluation['details_gpt4'] ?? null,
                'evaluation_deepseek' => $parsedEvaluation['details_deepseek'] ?? null,
                'evaluation_qwen' => $parsedEvaluation['details_qwen'] ?? null,
                'note_gpt4' => $parsedEvaluation['note_gpt4'] ?? null,
                'note_deepseek' => $parsedEvaluation['note_deepseek'] ?? null,
                'note_qwen' => $parsedEvaluation['note_qwen'] ?? null,
                'meilleure_ia' => $parsedEvaluation['meilleure_ia'] ?? null,
                'commentaire_global' => $parsedEvaluation['commentaire_global'] ?? 'Évaluation de programmation générée automatiquement',
                'token_usage_evaluation' => $evaluationResponse['response']['usage']['total_tokens'] ?? null,
                'response_time_evaluation' => $evaluationResponse['response_time'] ?? null,
                // 🎯 PAS DE WOLFRAM POUR LA PROGRAMMATION
                'wolfram_reference' => null,
                'wolfram_response_time' => null,
                'wolfram_status' => 'not_applicable'
            ]);

            \Log::info('✅ ÉVALUATION PROGRAMMATION CRÉÉE', [
                'question_id' => $question->id,
                'evaluation_id' => $evaluation->id,
                'wolfram_used' => false,
                'type_saved' => $evaluation->evaluation_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Évaluation de programmation générée avec succès',
                'evaluation_id' => $evaluation->id,
                'evaluation_type' => 'programming',
                'evaluation' => [
                    'note_gpt4' => $evaluation->note_gpt4,
                    'note_deepseek' => $evaluation->note_deepseek,
                    'note_qwen' => $evaluation->note_qwen,
                    'meilleure_ia' => $evaluation->meilleure_ia,
                    'commentaire_global' => $evaluation->commentaire_global,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ ERREUR ÉVALUATION PROGRAMMATION', [
                'question_id' => $question->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'évaluation de programmation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Génère le prompt d'évaluation mathématique pour Claude
     */
    protected function generateMathEvaluationPrompt(string $question, array $responses, ?string $wolframReference): string
    {
        $wolframSection = '';
        if ($wolframReference) {
            $wolframSection = "\n**RÉFÉRENCE WOLFRAM ALPHA :**\n{$wolframReference}\n";
        } else {
            $wolframSection = "\n**RÉFÉRENCE WOLFRAM ALPHA :** Non disponible (évaluation basée uniquement sur la justesse mathématique)\n";
        }

        return "Tu es un expert en évaluation mathématique et logique.

Analyse la question suivante et les 3 réponses fournies par différentes IA :

**QUESTION MATHÉMATIQUE :**
{$question}
{$wolframSection}
**RÉPONSE GPT-4 :**
{$responses['gpt4']}

**RÉPONSE DEEPSEEK :**
{$responses['deepseek']}

**RÉPONSE QWEN :**
{$responses['qwen']}

**INSTRUCTIONS D'ÉVALUATION MATHÉMATIQUE :**
Évalue chaque réponse selon ces 6 critères. IMPORTANT : Donne une note sur 2 points pour chaque critère ET une analyse textuelle :

1. **Cohérence avec la Référence** (2 points) : L'IA suit-elle la logique ou les résultats donnés par Wolfram Alpha ?
2. **Justesse Mathématique** (2 points) : Les calculs, raisonnements et conclusions sont-ils corrects ?
3. **Clarté de l'Explication** (2 points) : L'argumentation est-elle bien structurée, compréhensible, pédagogique ?
4. **Notation et Rigueur** (2 points) : L'IA respecte-t-elle les conventions mathématiques, la précision, la syntaxe formelle ?
5. **Pertinence du Raisonnement** (2 points) : L'approche logique adoptée est-elle pertinente et bien choisie ?
6. **Absence d'Hallucination** (2 points) : L'IA évite-t-elle d'inventer des résultats ou raisonnements faux, non démontrés, ou fictifs ?

**FORMAT DE RÉPONSE OBLIGATOIRE :**
```json
{
  \"comparaison\": {
    \"gpt-4\": {
      \"coherence_reference_note\": 2,
      \"coherence_reference\": \"Analyse de la cohérence avec Wolfram Alpha...\",
      \"justesse_math_note\": 2,
      \"justesse_math\": \"Évaluation de la justesse mathématique...\",
      \"clarte_explication_note\": 1,
      \"clarte_explication\": \"Analyse de la clarté de l'explication...\",
      \"notation_rigueur_note\": 2,
      \"notation_rigueur\": \"Évaluation de la notation et rigueur...\",
      \"pertinence_raisonnement_note\": 1,
      \"pertinence_raisonnement\": \"Analyse de la pertinence du raisonnement...\",
      \"hallucination_note\": 2,
      \"hallucination\": \"Non détectée / Présente: détail...\",
      \"note_sur_10\": 8.3,
      \"commentaire\": \"Analyse détaillée des forces et faiblesses\"
    },
    \"deepseek\": {
      \"coherence_reference_note\": 1,
      \"coherence_reference\": \"...\",
      \"justesse_math_note\": 2,
      \"justesse_math\": \"...\",
      \"clarte_explication_note\": 1,
      \"clarte_explication\": \"...\",
      \"notation_rigueur_note\": 1,
      \"notation_rigueur\": \"...\",
      \"pertinence_raisonnement_note\": 2,
      \"pertinence_raisonnement\": \"...\",
      \"hallucination_note\": 1,
      \"hallucination\": \"...\",
      \"note_sur_10\": 6.7,
      \"commentaire\": \"Analyse détaillée des forces et faiblesses\"
    },
    \"qwen\": {
      \"coherence_reference_note\": 0,
      \"coherence_reference\": \"...\",
      \"justesse_math_note\": 1,
      \"justesse_math\": \"...\",
      \"clarte_explication_note\": 2,
      \"clarte_explication\": \"...\",
      \"notation_rigueur_note\": 1,
      \"notation_rigueur\": \"...\",
      \"pertinence_raisonnement_note\": 1,
      \"pertinence_raisonnement\": \"...\",
      \"hallucination_note\": 1,
      \"hallucination\": \"...\",
      \"note_sur_10\": 5.0,
      \"commentaire\": \"Analyse détaillée des forces et faiblesses\"
    }
  },
  \"meilleure_ia\": \"gpt-4\",
  \"commentaire_global\": \"Comparaison globale des 3 réponses mathématiques et justification du choix\"
}
```

RÈGLES IMPORTANTES :
- Chaque critère_note doit être un nombre entre 0 et 2
- La note_sur_10 est calculée comme : (somme des 6 notes sur 2) * 10 / 12
- Sois strict et objectif dans l'évaluation
- Si Wolfram Alpha n'est pas disponible, évalue \"coherence_reference\" selon la logique mathématique générale

IMPORTANT : Réponds UNIQUEMENT avec le JSON valide, sans texte supplémentaire.";
    }

    /**
     * Génère le prompt d'évaluation pour la programmation (SANS Wolfram Alpha)
     */
    protected function generateProgrammingEvaluationPrompt(string $question, array $responses): string
    {
        return "Tu es un expert en évaluation de code et de solutions informatiques.

Analyse la question suivante et les 3 réponses fournies par différentes IA :

**QUESTION DE PROGRAMMATION :**
{$question}

**RÉPONSE GPT-4 :**
{$responses['gpt4']}

**RÉPONSE DEEPSEEK :**
{$responses['deepseek']}

**RÉPONSE QWEN :**
{$responses['qwen']}

**INSTRUCTIONS D'ÉVALUATION PROGRAMMATION :**
Évalue chaque réponse selon ces 5 critères. IMPORTANT : Donne une note sur 2 points pour chaque critère ET une analyse textuelle :

1. **Correctitude** (2 points) : Exactitude technique de la solution, absence de bugs
2. **Qualité du code** (2 points) : Lisibilité, structure, bonnes pratiques de programmation
3. **Modularité** (2 points) : Organisation et réutilisabilité du code, architecture
4. **Pertinence** (2 points) : Adaptation à la question posée, réponse au besoin
5. **Explication** (2 points) : Clarté des explications fournies, pédagogie

**FORMAT DE RÉPONSE OBLIGATOIRE :**
```json
{
  \"gpt4\": {
    \"correctitude_note\": 2,
    \"correctitude\": \"Analyse de la correctitude technique...\",
    \"qualite_code_note\": 1,
    \"qualite_code\": \"Évaluation de la qualité du code...\",
    \"modularite_note\": 2,
    \"modularite\": \"Analyse de la modularité...\",
    \"pertinence_note\": 2,
    \"pertinence\": \"Évaluation de la pertinence...\",
    \"explication_note\": 1,
    \"explication\": \"Analyse de la qualité des explications...\",
    \"note_totale\": 8.0,
    \"commentaire\": \"Analyse détaillée des forces et faiblesses\"
  },
  \"deepseek\": {
    \"correctitude_note\": 1,
    \"correctitude\": \"...\",
    \"qualite_code_note\": 2,
    \"qualite_code\": \"...\",
    \"modularite_note\": 1,
    \"modularite\": \"...\",
    \"pertinence_note\": 2,
    \"pertinence\": \"...\",
    \"explication_note\": 1,
    \"explication\": \"...\",
    \"note_totale\": 7.0,
    \"commentaire\": \"Analyse détaillée des forces et faiblesses\"
  },
  \"qwen\": {
    \"correctitude_note\": 1,
    \"correctitude\": \"...\",
    \"qualite_code_note\": 1,
    \"qualite_code\": \"...\",
    \"modularite_note\": 0,
    \"modularite\": \"...\",
    \"pertinence_note\": 2,
    \"pertinence\": \"...\",
    \"explication_note\": 2,
    \"explication\": \"...\",
    \"note_totale\": 6.0,
    \"commentaire\": \"Analyse détaillée des forces et faiblesses\"
  },
  \"meilleure_ia\": \"gpt4\",
  \"commentaire_global\": \"Comparaison globale des 3 réponses de programmation et justification du choix\"
}
```

RÈGLES IMPORTANTES :
- Chaque critère_note doit être un nombre entre 0 et 2
- La note_totale est calculée comme : (somme des 5 notes sur 2) * 10 / 10
- Sois strict et objectif dans l'évaluation du code
- Privilégie les solutions qui fonctionnent et sont bien expliquées

IMPORTANT : Réponds UNIQUEMENT avec le JSON valide, sans texte supplémentaire.";
    }

    /**
     * Parse la réponse d'évaluation mathématique de Claude
     */
    protected function parseMathEvaluationResponse(string $response): array
    {
        try {
            // Nettoyer la réponse pour extraire le JSON
            $response = trim($response);

            // Rechercher le JSON entre ```json et ```
            if (preg_match('/```json\s*(.*?)\s*```/s', $response, $matches)) {
                $jsonString = $matches[1];
            } else {
                // Essayer de trouver le JSON directement
                $jsonString = $response;
            }

            // Decoder le JSON
            $parsed = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Erreur de parsing JSON: ' . json_last_error_msg());
            }

            // Fonction helper pour extraire les détails avec notes par critère
            $extractDetails = function($aiData) {
                if (!is_array($aiData)) return null;

                return [
                    // Notes numériques par critère (0-2 points chacun)
                    'coherence_reference' => $this->validateCriterionNote($aiData['coherence_reference_note'] ?? 0),
                    'justesse_math' => $this->validateCriterionNote($aiData['justesse_math_note'] ?? 0),
                    'clarte_explication' => $this->validateCriterionNote($aiData['clarte_explication_note'] ?? 0),
                    'notation_rigueur' => $this->validateCriterionNote($aiData['notation_rigueur_note'] ?? 0),
                    'pertinence_raisonnement' => $this->validateCriterionNote($aiData['pertinence_raisonnement_note'] ?? 0),
                    'hallucination' => $this->validateCriterionNote($aiData['hallucination_note'] ?? 0),

                    // Analyses textuelles
                    'coherence_reference_analyse' => trim($aiData['coherence_reference'] ?? ''),
                    'justesse_math_analyse' => trim($aiData['justesse_math'] ?? ''),
                    'clarte_explication_analyse' => trim($aiData['clarte_explication'] ?? ''),
                    'notation_rigueur_analyse' => trim($aiData['notation_rigueur'] ?? ''),
                    'pertinence_raisonnement_analyse' => trim($aiData['pertinence_raisonnement'] ?? ''),
                    'hallucination_analyse' => trim($aiData['hallucination'] ?? ''),

                    // Note globale et commentaire
                    'note_totale' => $this->validateNote($aiData['note_sur_10'] ?? 0),
                    'commentaire' => trim($aiData['commentaire'] ?? ''),
                ];
            };

            // Extraire et valider les données
            $result = [
                'note_gpt4' => isset($parsed['comparaison']['gpt-4']['note_sur_10']) ?
                    $this->validateNote($parsed['comparaison']['gpt-4']['note_sur_10']) : null,
                'note_deepseek' => isset($parsed['comparaison']['deepseek']['note_sur_10']) ?
                    $this->validateNote($parsed['comparaison']['deepseek']['note_sur_10']) : null,
                'note_qwen' => isset($parsed['comparaison']['qwen']['note_sur_10']) ?
                    $this->validateNote($parsed['comparaison']['qwen']['note_sur_10']) : null,
                'meilleure_ia' => $this->validateBestAI($parsed['meilleure_ia'] ?? null),
                'commentaire_global' => trim($parsed['commentaire_global'] ?? 'Évaluation mathématique générée automatiquement'),

                // Détails complets avec notes par critère
                'details_gpt4' => $extractDetails($parsed['comparaison']['gpt-4'] ?? null),
                'details_deepseek' => $extractDetails($parsed['comparaison']['deepseek'] ?? null),
                'details_qwen' => $extractDetails($parsed['comparaison']['qwen'] ?? null),
            ];

            \Log::info('Évaluation mathématique parsée avec succès', [
                'notes_globales' => [
                    'gpt4' => $result['note_gpt4'],
                    'deepseek' => $result['note_deepseek'],
                    'qwen' => $result['note_qwen']
                ],
                'details_disponibles' => [
                    'gpt4' => !is_null($result['details_gpt4']),
                    'deepseek' => !is_null($result['details_deepseek']),
                    'qwen' => !is_null($result['details_qwen'])
                ]
            ]);

            return $result;

        } catch (\Exception $e) {
            \Log::warning('Erreur de parsing de l\'évaluation mathématique', [
                'response' => $response,
                'error' => $e->getMessage()
            ]);

            // Fallback avec des valeurs par défaut
            return [
                'note_gpt4' => 7,
                'note_deepseek' => 7,
                'note_qwen' => 7,
                'meilleure_ia' => 'gpt4',
                'commentaire_global' => 'Évaluation mathématique générée automatiquement - erreur de parsing de la réponse originale.',
                'details_gpt4' => null,
                'details_deepseek' => null,
                'details_qwen' => null,
            ];
        }
    }

    /**
     * Valide une note de critère (doit être entre 0 et 2)
     */
    protected function validateCriterionNote($note): int
    {
        if (!is_numeric($note)) {
            return 0;
        }

        $note = (int) $note;
        return max(0, min(2, $note));
    }

    /**
     * Parse la réponse d'évaluation de GPT-4 (méthode existante pour la programmation)
     */
    protected function parseEvaluationResponse(string $response): array
    {
        try {
            // Nettoyer la réponse pour extraire le JSON
            $response = trim($response);

            // Rechercher le JSON entre ```json et ```
            if (preg_match('/```json\s*(.*?)\s*```/s', $response, $matches)) {
                $jsonString = $matches[1];
            } else {
                // Essayer de trouver le JSON directement
                $jsonString = $response;
            }

            // Decoder le JSON
            $parsed = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Erreur de parsing JSON: ' . json_last_error_msg());
            }

            // Fonction helper pour extraire les détails avec notes par critère (programmation)
            $extractProgrammingDetails = function($aiData) {
                if (!is_array($aiData)) return null;

                return [
                    // Notes numériques par critère (0-2 points chacun)
                    'correctitude' => $this->validateCriterionNote($aiData['correctitude_note'] ?? 0),
                    'qualite_code' => $this->validateCriterionNote($aiData['qualite_code_note'] ?? 0),
                    'modularite' => $this->validateCriterionNote($aiData['modularite_note'] ?? 0),
                    'pertinence' => $this->validateCriterionNote($aiData['pertinence_note'] ?? 0),
                    'explication' => $this->validateCriterionNote($aiData['explication_note'] ?? 0),

                    // Analyses textuelles
                    'correctitude_analyse' => trim($aiData['correctitude'] ?? ''),
                    'qualite_code_analyse' => trim($aiData['qualite_code'] ?? ''),
                    'modularite_analyse' => trim($aiData['modularite'] ?? ''),
                    'pertinence_analyse' => trim($aiData['pertinence'] ?? ''),
                    'explication_analyse' => trim($aiData['explication'] ?? ''),

                    // Note globale et commentaire
                    'note_totale' => $this->validateNote($aiData['note_totale'] ?? 0),
                    'commentaire' => trim($aiData['commentaire'] ?? ''),
                ];
            };

            // Extraire et valider les données
            $result = [
                'note_gpt4' => isset($parsed['gpt4']['note_totale']) ? $this->validateNote($parsed['gpt4']['note_totale']) : null,
                'note_deepseek' => isset($parsed['deepseek']['note_totale']) ? $this->validateNote($parsed['deepseek']['note_totale']) : null,
                'note_qwen' => isset($parsed['qwen']['note_totale']) ? $this->validateNote($parsed['qwen']['note_totale']) : null,
                'meilleure_ia' => $this->validateBestAI($parsed['meilleure_ia'] ?? null),
                'commentaire_global' => trim($parsed['commentaire_global'] ?? 'Évaluation de programmation générée automatiquement'),

                // Détails complets avec notes par critère
                'details_gpt4' => $extractProgrammingDetails($parsed['gpt4'] ?? null),
                'details_deepseek' => $extractProgrammingDetails($parsed['deepseek'] ?? null),
                'details_qwen' => $extractProgrammingDetails($parsed['qwen'] ?? null),
            ];

            \Log::info('Évaluation de programmation parsée avec succès', [
                'notes_globales' => [
                    'gpt4' => $result['note_gpt4'],
                    'deepseek' => $result['note_deepseek'],
                    'qwen' => $result['note_qwen']
                ],
                'details_disponibles' => [
                    'gpt4' => !is_null($result['details_gpt4']),
                    'deepseek' => !is_null($result['details_deepseek']),
                    'qwen' => !is_null($result['details_qwen'])
                ]
            ]);

            return $result;

        } catch (\Exception $e) {
            \Log::warning('Erreur de parsing de l\'évaluation de programmation', [
                'response' => $response,
                'error' => $e->getMessage()
            ]);

            // Fallback avec des valeurs par défaut
            return [
                'note_gpt4' => 7,
                'note_deepseek' => 7,
                'note_qwen' => 7,
                'meilleure_ia' => 'gpt4',
                'commentaire_global' => 'Évaluation de programmation générée automatiquement - erreur de parsing de la réponse originale.',
                'details_gpt4' => null,
                'details_deepseek' => null,
                'details_qwen' => null,
            ];
        }
    }

    /**
     * Retraite une question (regenere l'évaluation)
     */
    public function reprocess(Question $question)
    {
        try {
            // Vérifier les permissions
            if ($question->user_id !== Auth::id()) {
                return response()->json(['success' => false, 'message' => 'Accès non autorisé'], 403);
            }

            // Supprimer l'ancienne évaluation si elle existe
            Evaluation::where('question_id', $question->id)->delete();

            // Relancer l'évaluation
            return $this->evaluateQuestion($question);

        } catch (\Exception $e) {
            \Log::error('Erreur lors du retraitement', [
                'question_id' => $question->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du retraitement'
            ], 500);
        }
    }

    /**
     * Obtient le statut d'une évaluation
     */
    public function getStatus(Question $question)
    {
        try {
            if ($question->user_id !== Auth::id()) {
                return response()->json(['error' => 'Non autorisé'], 403);
            }

            $question->load(['evaluation', 'iaResponses', 'domain']);

            $hasEvaluation = $question->evaluation ? true : false;
            $canEvaluate = $question->isEvaluableQuestion() && $question->iaResponses->count() >= 3;

            // CORRECTION : Utiliser la logique corrigée de détection
            $isProgramming = $question->isProgrammingQuestion();
            $isMathematical = $question->isMathematicalQuestion();
            $isTranslation = $this->isTranslationQuestion($question);
            $evaluationType = $question->getEvaluationType();

            \Log::info('GetStatus - Diagnostic', [
                'question_id' => $question->id,
                'domain_name' => $question->domain->name,
                'is_programming' => $isProgramming,
                'is_mathematical' => $isMathematical,
                'is_translation' => $isTranslation,
                'evaluation_type' => $evaluationType
            ]);

            $response = [
                'success' => true,
                'has_evaluation' => $hasEvaluation,
                'responses_count' => $question->iaResponses->count(),
                'can_evaluate' => $canEvaluate,
                'evaluation_type' => $evaluationType,
                'is_programming' => $isProgramming,
                'is_mathematical' => $isMathematical,
                'is_translation' => $isTranslation,
            ];

            // Si une évaluation existe, inclure ses détails
            if ($hasEvaluation && $question->evaluation) {
                $evaluation = $question->evaluation;

                $response['evaluation'] = [
                    'id' => $evaluation->id,
                    'is_complete' => $evaluation->isComplete(),
                    'created_at' => $evaluation->created_at,
                    'type' => $evaluation->evaluation_type ?? 'programming',

                    // Données pour l'affichage immédiat
                    'best_ai' => $evaluation->meilleure_ia,
                    'best_ai_name' => $evaluation->best_ai_name,
                    'gpt4_score' => $evaluation->note_gpt4,
                    'deepseek_score' => $evaluation->note_deepseek,
                    'qwen_score' => $evaluation->note_qwen,
                    'average_score' => $evaluation->average_score,

                    // Données pour Wolfram (si mathématiques)
                    'has_wolfram_reference' => $evaluation->hasWolframReference(),
                    'wolfram_status' => $evaluation->wolfram_status ?? 'not_applicable',

                    // Commentaire global
                    'global_comment' => $evaluation->commentaire_global,

                    // Métadonnées
                    'token_usage' => $evaluation->token_usage_evaluation,
                    'response_time' => $evaluation->response_time_evaluation,
                ];
            } else {
                $response['evaluation'] = null;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            \Log::error('Erreur getStatus evaluation', [
                'question_id' => $question->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtient les statistiques d'évaluation
     */
    public function getStats()
    {
        try {
            $userId = Auth::id();

            $stats = [
                'total_evaluations' => Evaluation::whereHas('question', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->count(),

                'programming_evaluations' => Evaluation::whereHas('question', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->where('evaluation_type', 'programming')->count(),

                'math_evaluations' => Evaluation::whereHas('question', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->where('evaluation_type', 'mathematics')->count(),

                'translation_evaluations' => Evaluation::whereHas('question', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->where('evaluation_type', 'translation')->count(),

                'complete_evaluations' => Evaluation::whereHas('question', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                })->whereNotNull('note_gpt4')
                    ->whereNotNull('note_deepseek')
                    ->whereNotNull('note_qwen')
                    ->count(),

                'pending_evaluations' => Question::where('user_id', $userId)
                    ->where(function($q) {
                        $q->whereHas('domain', function($query) {
                            $query->where('name', 'LIKE', '%programmation%')
                                ->orWhere('name', 'LIKE', '%programming%')
                                ->orWhere('name', 'LIKE', '%code%')
                                ->orWhere('name', 'LIKE', '%math%')
                                ->orWhere('name', 'LIKE', '%logique%')
                                ->orWhere('name', 'LIKE', '%traduction%')
                                ->orWhere('name', 'LIKE', '%translation%');
                        });
                    })
                    ->whereDoesntHave('evaluation')
                    ->whereHas('iaResponses', function($q) {
                        $q->havingRaw('COUNT(*) >= 3');
                    })
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * MÉTHODES UTILITAIRES
     */

    /**
     * Nettoie la réponse en supprimant les commentaires
     */
    protected function cleanResponse(string $response): string
    {
        // Supprimer les commentaires HTML
        $response = preg_replace('/<!--.*?-->/s', '', $response);

        // Supprimer les commentaires de style // ou /* */
        $response = preg_replace('/\/\/.*$/m', '', $response);
        $response = preg_replace('/\/\*.*?\*\//s', '', $response);

        // Supprimer les commentaires markdown
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
     * Valide une note (doit être entre 0 et 10)
     */
    protected function validateNote($note): ?int
    {
        if (!is_numeric($note)) {
            return null;
        }

        $note = (int) $note;
        return max(0, min(10, $note));
    }

    /**
     * Valide le nom de la meilleure IA
     */
    protected function validateBestAI($bestAI): string
    {
        $validAIs = ['gpt4', 'deepseek', 'qwen', 'GPT-4', 'DeepSeek', 'Qwen', 'gpt-4'];

        if (!$bestAI || !in_array($bestAI, $validAIs)) {
            return 'gpt4'; // Fallback
        }

        // Normaliser le nom
        $bestAI = strtolower($bestAI);
        if (in_array($bestAI, ['gpt-4', 'gpt4', 'openai'])) {
            return 'gpt4';
        } elseif (in_array($bestAI, ['deepseek', 'deepseek-r1'])) {
            return 'deepseek';
        } elseif (in_array($bestAI, ['qwen', 'qwen-2.5'])) {
            return 'qwen';
        }

        return 'gpt4'; // Fallback
    }



    public function getSummary(Question $question)
    {
        try {
            if ($question->user_id !== Auth::id()) {
                return response()->json(['error' => 'Non autorisé'], 403);
            }

            $question->load(['evaluation']);

            if (!$question->evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune évaluation disponible'
                ]);
            }

            $evaluation = $question->evaluation;

            return response()->json([
                'success' => true,
                'summary' => [
                    'best_ai' => $evaluation->meilleure_ia,
                    'best_ai_display' => $evaluation->best_ai_name,
                    'scores' => [
                        'gpt4' => $evaluation->note_gpt4,
                        'deepseek' => $evaluation->note_deepseek,
                        'qwen' => $evaluation->note_qwen,
                    ],
                    'average' => $evaluation->average_score,
                    'type' => $evaluation->evaluation_type,
                    'has_wolfram' => $evaluation->hasWolframReference(),
                    'has_deepl' => $evaluation->evaluation_type === 'translation',
                    'created_at' => $evaluation->created_at->format('d/m/Y H:i'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function triggerManualEvaluation(Question $question)
    {
        try {
            // Vérifications de sécurité
            if ($question->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé à cette question'
                ], 403);
            }

            if (!$question->isEvaluableQuestion()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette question n\'est pas évaluable'
                ], 400);
            }

            if (!$question->hasAllAIResponses()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toutes les réponses IA ne sont pas encore disponibles'
                ], 400);
            }

            // Supprimer l'ancienne évaluation si elle existe
            if ($question->evaluation) {
                $question->evaluation->delete();
                \Log::info('Ancienne évaluation supprimée pour re-évaluation', [
                    'question_id' => $question->id
                ]);
            }

            // Déclencher l'évaluation
            $result = $this->evaluateQuestion($question);

            if ($result instanceof \Illuminate\Http\JsonResponse) {
                return $result;
            }

            return response()->json([
                'success' => true,
                'message' => 'Évaluation déclenchée avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur déclenchement manuel évaluation', [
                'question_id' => $question->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du déclenchement de l\'évaluation'
            ], 500);
        }
    }




    public function evaluate(Request $request, Question $question)
    {
        // Déléguer à evaluateQuestion()
        return $this->evaluateQuestion($question);
    }


}
