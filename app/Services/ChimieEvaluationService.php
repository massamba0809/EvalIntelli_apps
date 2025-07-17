<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Evaluation;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service d'évaluation spécialisé pour le domaine Chimie - VERSION CORRIGÉE
 */
class ChimieEvaluationService
{
    protected OpenRouterService $openRouter;
    protected WolframAlphaService $wolfram;
    protected DeepLService $deepL;

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
     * Évalue une question de chimie avec gestion d'erreurs renforcée
     */
    public function evaluateChemistryQuestion(Question $question): array
    {
        try {
            Log::info('🧪 DÉBUT ÉVALUATION CHIMIE (FORMAT JSON)', [
                'question_id' => $question->id
            ]);

            // Étape 1 : Récupérer les réponses
            $responses = $this->getAIResponsesSafe($question);

            // Étape 2 : Wolfram Alpha (optionnel pour chimie)
            $wolframResult = $this->getWolframReferenceSafe($question->content);

            // Étape 3 : Évaluation Claude avec JSON
            $prompt = $this->generateChemistryEvaluationPrompt(
                $question->content,
                $responses,
                $wolframResult['reference']
            );

            $claudeResponse = $this->openRouter->sendMessage($prompt, 'anthropic/claude-3.5-sonnet');

            if (!$claudeResponse['success']) {
                throw new \Exception('Erreur Claude: ' . ($claudeResponse['response'] ?? 'Unknown error'));
            }

            $content = $claudeResponse['response']['choices'][0]['message']['content'] ?? '';

            // Étape 4 : Parser JSON (identique aux autres domaines)
            $parsedEvaluation = $this->parseChemistryEvaluationResponse($content);

            // Étape 5 : Sauvegarder (format uniforme)
            $evaluation = Evaluation::create([
                'question_id' => $question->id,
                'evaluation_type' => 'chemistry',

                // Notes principales
                'note_gpt4' => $parsedEvaluation['note_gpt4'],
                'note_deepseek' => $parsedEvaluation['note_deepseek'],
                'note_qwen' => $parsedEvaluation['note_qwen'],

                // Détails JSON (identique aux autres domaines)
                'evaluation_gpt4' => json_encode($parsedEvaluation['evaluation_gpt4']),
                'evaluation_deepseek' => json_encode($parsedEvaluation['evaluation_deepseek']),
                'evaluation_qwen' => json_encode($parsedEvaluation['evaluation_qwen']),

                // Meilleure IA et commentaire
                'meilleure_ia' => $parsedEvaluation['meilleure_ia'],
                'commentaire_global' => $parsedEvaluation['commentaire_global'],

                // Métadonnées Wolfram
                'wolfram_reference' => $wolframResult['reference'],
                'wolfram_status' => $wolframResult['status'],
                'wolfram_response_time' => $wolframResult['response_time'],

                // Métadonnées Claude
                'token_usage_evaluation' => $claudeResponse['response']['usage']['total_tokens'] ?? null,
                'response_time_evaluation' => $claudeResponse['response_time'] ?? null,
            ]);

            Log::info('✅ ÉVALUATION CHIMIE TERMINÉE', [
                'question_id' => $question->id,
                'evaluation_id' => $evaluation->id,
                'best_ai' => $evaluation->meilleure_ia
            ]);

            return [
                'success' => true,
                'evaluation' => $evaluation,
                'evaluation_type' => 'chemistry',
                'details' => [
                    'wolfram_status' => $wolframResult['status'],
                    'claude_success' => true
                ]
            ];

        } catch (\Exception $e) {
            Log::error('❌ ERREUR ÉVALUATION CHIMIE', [
                'question_id' => $question->id,
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    public function evaluateQuestion(Question $question): array
    {
        // Alias pour evaluateChemistryQuestion pour compatibilité
        return $this->evaluateChemistryQuestion($question);
    }


    /**
     * Version sécurisée de isChemistryQuestion avec fallback
     */
    protected function isChemistryQuestionSafe(Question $question): bool
    {
        try {
            return $this->isChemistryQuestion($question);
        } catch (Exception $e) {
            Log::warning('⚠️ Erreur détection chimie, fallback false', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Récupération sécurisée des réponses IA
     */
    protected function getAIResponsesSafe(Question $question): array
    {
        try {
            $responsesByModel = $question->getResponsesByModel();
            $responses = [];

            $requiredModels = ['gpt4', 'deepseek', 'qwen'];
            foreach ($requiredModels as $model) {
                if (isset($responsesByModel[$model])) {
                    $responses[$model] = $this->sanitizeText($responsesByModel[$model]->response);
                } else {
                    throw new Exception("Réponse manquante pour le modèle {$model}");
                }
            }

            return $responses;
        } catch (Exception $e) {
            Log::error('❌ Erreur récupération réponses IA', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Appel Wolfram Alpha sécurisé avec fallback
     */
    protected function getWolframReferenceSafe(string $question): array
    {
        try {
            Log::info('🧪 Wolfram Alpha chimie - début', [
                'question' => $question,
                'question_length' => strlen($question)
            ]);

            // 🔧 UTILISER LE MÊME SYSTÈME QUE LES MATHÉMATIQUES
            // Au lieu de préparer spécialement pour chimie, utiliser le service Wolfram existant
            $wolframResponse = $this->wolfram->querySimple($question);

            if ($wolframResponse['status'] === 'success' && $wolframResponse['has_reference']) {
                $reference = $this->wolfram->formatResponseForEvaluation($wolframResponse['response']);

                Log::info('✅ Wolfram Alpha chimie réussi', [
                    'reference_length' => strlen($reference),
                    'api_used' => $wolframResponse['api_used'] ?? 'unknown',
                    'variant_used' => $wolframResponse['variant_used'] ?? 'unknown'
                ]);

                return [
                    'status' => 'success',
                    'reference' => $this->sanitizeText($reference),
                    'response_time' => $wolframResponse['response_time'] ?? null
                ];
            } else {
                Log::info('❌ Wolfram Alpha chimie échoué', [
                    'status' => $wolframResponse['status'],
                    'response' => substr($wolframResponse['response'] ?? '', 0, 100)
                ]);
            }

        } catch (Exception $e) {
            Log::warning('⚠️ Wolfram Alpha chimie exception', [
                'error' => $e->getMessage()
            ]);
        }

        // Fallback : pas de référence Wolfram
        return [
            'status' => 'no_reference',
            'reference' => null,
            'response_time' => null
        ];
    }

    /**
     * Prépare une question de chimie pour Wolfram Alpha
     */
    protected function prepareChemistryQuestionForWolfram(string $question): string
    {
        $question = trim($question);

        // Détecter le type de question chimique
        if ($this->isBalanceEquationQuestion($question)) {
            return $this->formatBalanceEquationForWolfram($question);
        }

        // Ajouter préfixe chimie pour les autres questions
        return "chemistry: {$question}";
    }

    /**
     * Vérifie si c'est une question d'équilibrage
     */
    protected function isBalanceEquationQuestion(string $question): bool
    {
        $balanceKeywords = [
            'balance', 'équilibrer', 'équilibrage', 'balance the equation'
        ];

        $questionLower = strtolower($question);

        foreach ($balanceKeywords as $keyword) {
            if (str_contains($questionLower, $keyword)) {
                return true;
            }
        }

        // Détecter les équations chimiques (A + B -> C)
        return preg_match('/[A-Z][a-z]?\d?\s*\+\s*[A-Z][a-z]?\d?\s*->\s*[A-Z][a-z]?\d?/', $question);
    }

    /**
     * Formate une équation chimique pour Wolfram
     */
    protected function formatBalanceEquationForWolfram(string $question): string
    {
        // Extraire l'équation chimique
        if (preg_match('/([A-Z][a-z]?\d?\s*\+\s*[A-Z][a-z]?\d?\s*->\s*[A-Z][a-z]?\d?)/', $question, $matches)) {
            $equation = $matches[1];

            // Formater pour Wolfram Alpha : -> devient =
            $formatted = str_replace('->', ' = ', $equation);
            $formatted = str_replace('  ', ' ', $formatted);

            return "balance chemical equation: {$formatted}";
        }

        // Si pas d'équation trouvée, formater la question entière
        $formatted = str_replace('->', ' = ', $question);
        return "balance chemical equation: {$formatted}";
    }

    /**
     * Traduction sécurisée pour Wolfram
     */
    protected function prepareQuestionForWolframSafe(string $question): string
    {
        try {
            if ($this->detectFrenchLanguage($question)) {
                $translated = $this->deepL->translate($question, 'EN', 'FR');
                if ($translated['success']) {
                    return $translated['translated_text'];
                }
            }
        } catch (Exception $e) {
            Log::warning('⚠️ Traduction échouée, question originale utilisée', ['error' => $e->getMessage()]);
        }

        return $question;
    }

    /**
     * Évaluation Claude sécurisée
     */
    protected function evaluateWithClaudeSafe(string $question, array $responses, array $wolframResult): array
    {
        try {
            $prompt = $this->generateSimplifiedChemistryPrompt($question, $responses, $wolframResult['reference']);

            $claudeResponse = $this->openRouter->sendMessage($prompt, 'anthropic/claude-3.5-sonnet');

            if ($claudeResponse['success']) {
                $content = $this->sanitizeText($claudeResponse['response']['choices'][0]['message']['content'] ?? '');

                return [
                    'status' => 'success',
                    'content' => $content,
                    'response_time' => $claudeResponse['response_time'] ?? null
                ];
            } else {
                throw new Exception('Erreur Claude: ' . ($claudeResponse['response'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            Log::error('❌ Erreur Claude chimie', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Prompt simplifié et robuste pour l'évaluation chimie
     */
    protected function generateChemistryEvaluationPrompt(string $question, array $responses, ?string $wolframRef): string
    {
        $wolframSection = $wolframRef ?
            "\n**RÉFÉRENCE WOLFRAM ALPHA :**\n{$wolframRef}\n" :
            "\n**RÉFÉRENCE WOLFRAM ALPHA :** Non disponible\n";

        return "Tu es un expert en chimie et évaluation scientifique.

Analyse la question suivante et les 3 réponses fournies par différentes IA :

**QUESTION DE CHIMIE :**
{$question}

{$wolframSection}

**RÉPONSE GPT-4 :**
{$responses['gpt4']}

**RÉPONSE DEEPSEEK :**
{$responses['deepseek']}

**RÉPONSE QWEN :**
{$responses['qwen']}

**INSTRUCTIONS D'ÉVALUATION CHIMIE :**
Évalue chaque réponse selon ces 6 critères. IMPORTANT : Donne une note sur 2 points pour chaque critère ET une analyse textuelle :

1. **Exactitude scientifique** (2 points) : Correction des formules, équations et concepts chimiques
2. **Complétude** (2 points) : Réponse complète qui couvre tous les aspects de la question
3. **Clarté des explications** (2 points) : Facilité de compréhension et structure logique
4. **Terminologie chimique** (2 points) : Utilisation correcte du vocabulaire scientifique
5. **Cohérence logique** (2 points) : Raisonnement chimique cohérent et logique
6. **Références/Sources** (2 points) : Mention de sources fiables ou principes établis

**FORMAT DE RÉPONSE OBLIGATOIRE :**
```json
{
  \"gpt4\": {
    \"exactitude_scientifique_note\": 2,
    \"exactitude_scientifique\": \"Analyse de l'exactitude scientifique...\",
    \"completude_note\": 1,
    \"completude\": \"Évaluation de la complétude...\",
    \"clarte_explications_note\": 2,
    \"clarte_explications\": \"Analyse de la clarté...\",
    \"terminologie_chimique_note\": 2,
    \"terminologie_chimique\": \"Évaluation de la terminologie...\",
    \"coherence_logique_note\": 1,
    \"coherence_logique\": \"Analyse de la cohérence...\",
    \"references_sources_note\": 0,
    \"references_sources\": \"Évaluation des références...\",
    \"note_sur_10\": 8.0,
    \"commentaire\": \"Analyse détaillée des forces et faiblesses\"
  },
  \"deepseek\": {
    \"exactitude_scientifique_note\": 1,
    \"exactitude_scientifique\": \"...\",
    \"completude_note\": 2,
    \"completude\": \"...\",
    \"clarte_explications_note\": 1,
    \"clarte_explications\": \"...\",
    \"terminologie_chimique_note\": 1,
    \"terminologie_chimique\": \"...\",
    \"coherence_logique_note\": 2,
    \"coherence_logique\": \"...\",
    \"references_sources_note\": 0,
    \"references_sources\": \"...\",
    \"note_sur_10\": 7.0,
    \"commentaire\": \"Analyse détaillée des forces et faiblesses\"
  },
  \"qwen\": {
    \"exactitude_scientifique_note\": 1,
    \"exactitude_scientifique\": \"...\",
    \"completude_note\": 1,
    \"completude\": \"...\",
    \"clarte_explications_note\": 1,
    \"clarte_explications\": \"...\",
    \"terminologie_chimique_note\": 1,
    \"terminologie_chimique\": \"...\",
    \"coherence_logique_note\": 1,
    \"coherence_logique\": \"...\",
    \"references_sources_note\": 0,
    \"references_sources\": \"...\",
    \"note_sur_10\": 5.0,
    \"commentaire\": \"Analyse détaillée des forces et faiblesses\"
  },
  \"meilleure_ia\": \"gpt4\",
  \"commentaire_global\": \"Comparaison globale des 3 réponses de chimie et justification du choix\"
}
```

RÈGLES IMPORTANTES :
- Chaque critère_note doit être un nombre entre 0 et 2
- La note_sur_10 est calculée comme : (somme des 6 notes sur 2) * 10 / 12
- Sois strict et objectif dans l'évaluation scientifique
- Privilégie les réponses scientifiquement correctes et bien expliquées

IMPORTANT : Réponds UNIQUEMENT avec le JSON valide, sans texte supplémentaire.";
    }

    /**
     * Sauvegarde sécurisée avec gestion d'erreurs UTF-8
     */
    protected function saveEvaluationSafe(Question $question, array $claudeResult, array $wolframResult): Evaluation
    {
        try {
            // Parser les scores avec fallback
            $parsedScores = $this->parseClaudeResponseSafe($claudeResult['content']);

            // Supprimer l'ancienne évaluation si elle existe
            if ($question->evaluation) {
                $question->evaluation->delete();
            }

            // Créer l'évaluation avec données sécurisées
            $evaluation = Evaluation::create([
                'question_id' => $question->id,
                'evaluation_type' => 'chemistry',

                // Évaluations JSON sécurisées
                'evaluation_gpt4' => $this->createSafeJsonEvaluation($parsedScores['gpt4']),
                'evaluation_deepseek' => $this->createSafeJsonEvaluation($parsedScores['deepseek']),
                'evaluation_qwen' => $this->createSafeJsonEvaluation($parsedScores['qwen']),

                // Notes
                'note_gpt4' => $parsedScores['gpt4']['total'],
                'note_deepseek' => $parsedScores['deepseek']['total'],
                'note_qwen' => $parsedScores['qwen']['total'],

                // Meilleure IA
                'meilleure_ia' => $parsedScores['best_ai'],

                // Commentaire global
                'commentaire_global' => $this->sanitizeText($parsedScores['global_comment']),

                // Métadonnées Wolfram
                'wolfram_reference' => $wolframResult['reference'] ? $this->sanitizeText($wolframResult['reference']) : null,
                'wolfram_status' => $wolframResult['status'],
                'wolfram_response_time' => $wolframResult['response_time'],

                // Métadonnées Claude
                'token_usage_evaluation' => $claudeResult['token_usage'] ?? null,
                'response_time_evaluation' => $claudeResult['response_time'] ?? null
            ]);

            return $evaluation;

        } catch (Exception $e) {
            Log::error('❌ Erreur sauvegarde évaluation chimie', [
                'question_id' => $question->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Parsing sécurisé de la réponse Claude avec fallbacks
     */
    protected function parseChemistryEvaluationResponse(string $response): array
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

            // Fonction helper pour extraire les détails avec notes par critère (chimie)
            $extractChemistryDetails = function($aiData) {
                if (!is_array($aiData)) return null;

                return [
                    // Notes numériques par critère (0-2 points chacun)
                    'exactitude_scientifique' => $this->validateCriterionNote($aiData['exactitude_scientifique_note'] ?? 0),
                    'completude' => $this->validateCriterionNote($aiData['completude_note'] ?? 0),
                    'clarte_explications' => $this->validateCriterionNote($aiData['clarte_explications_note'] ?? 0),
                    'terminologie_chimique' => $this->validateCriterionNote($aiData['terminologie_chimique_note'] ?? 0),
                    'coherence_logique' => $this->validateCriterionNote($aiData['coherence_logique_note'] ?? 0),
                    'references_sources' => $this->validateCriterionNote($aiData['references_sources_note'] ?? 0),

                    // Analyses textuelles
                    'exactitude_scientifique_analyse' => trim($aiData['exactitude_scientifique'] ?? ''),
                    'completude_analyse' => trim($aiData['completude'] ?? ''),
                    'clarte_explications_analyse' => trim($aiData['clarte_explications'] ?? ''),
                    'terminologie_chimique_analyse' => trim($aiData['terminologie_chimique'] ?? ''),
                    'coherence_logique_analyse' => trim($aiData['coherence_logique'] ?? ''),
                    'references_sources_analyse' => trim($aiData['references_sources'] ?? ''),

                    // Note globale et commentaire
                    'note_totale' => $this->validateNote($aiData['note_sur_10'] ?? 0),
                    'commentaire' => trim($aiData['commentaire'] ?? ''),
                ];
            };

            // Extraire et valider les données
            $result = [
                'note_gpt4' => isset($parsed['gpt4']['note_sur_10']) ?
                    $this->validateNote($parsed['gpt4']['note_sur_10']) : null,
                'note_deepseek' => isset($parsed['deepseek']['note_sur_10']) ?
                    $this->validateNote($parsed['deepseek']['note_sur_10']) : null,
                'note_qwen' => isset($parsed['qwen']['note_sur_10']) ?
                    $this->validateNote($parsed['qwen']['note_sur_10']) : null,

                // Évaluations détaillées
                'evaluation_gpt4' => $extractChemistryDetails($parsed['gpt4'] ?? []),
                'evaluation_deepseek' => $extractChemistryDetails($parsed['deepseek'] ?? []),
                'evaluation_qwen' => $extractChemistryDetails($parsed['qwen'] ?? []),

                // Meilleure IA et commentaire global
                'meilleure_ia' => $this->normalizeAIName($parsed['meilleure_ia'] ?? 'gpt4'),
                'commentaire_global' => trim($parsed['commentaire_global'] ?? 'Évaluation chimie générée automatiquement'),
            ];

            return $result;

        } catch (\Exception $e) {
            Log::error('❌ ERREUR PARSING CHIMIE JSON', [
                'error' => $e->getMessage(),
                'response_preview' => substr($response, 0, 500)
            ]);

            // Fallback en cas d'erreur
            return [
                'note_gpt4' => 5,
                'note_deepseek' => 5,
                'note_qwen' => 5,
                'evaluation_gpt4' => $this->getFallbackChemistryDetails(),
                'evaluation_deepseek' => $this->getFallbackChemistryDetails(),
                'evaluation_qwen' => $this->getFallbackChemistryDetails(),
                'meilleure_ia' => 'gpt4',
                'commentaire_global' => 'Erreur lors du parsing, évaluation par défaut appliquée',
            ];
        }
    }


    protected function validateCriterionNote($note): int
    {
        $note = (int)$note;
        return max(0, min(2, $note));
    }

// Validation des notes totales (0-10 points)
    protected function validateNote($note): float
    {
        $note = (float)$note;
        return max(0, min(10, $note));
    }


    protected function getFallbackChemistryDetails(): array
    {
        return [
            'exactitude_scientifique' => 1,
            'completude' => 1,
            'clarte_explications' => 1,
            'terminologie_chimique' => 1,
            'coherence_logique' => 1,
            'references_sources' => 0,
            'exactitude_scientifique_analyse' => 'Analyse non disponible',
            'completude_analyse' => 'Analyse non disponible',
            'clarte_explications_analyse' => 'Analyse non disponible',
            'terminologie_chimique_analyse' => 'Analyse non disponible',
            'coherence_logique_analyse' => 'Analyse non disponible',
            'references_sources_analyse' => 'Analyse non disponible',
            'note_totale' => 5.0,
            'commentaire' => 'Évaluation par défaut appliquée suite à une erreur',
        ];
    }




    protected function parseDetailedCriteria(string $commentaire): array
    {
        $criteres = [];

        // Patterns pour extraire les critères individuels
        $criteriaPatterns = [
            'exactitude_scientifique' => '/Exactitude scientifique\s*:?\s*(\d+)\/2\s*([^.\n]*)/i',
            'completude_reponse' => '/Complétude\s*:?\s*(\d+)\/2\s*([^.\n]*)/i',
            'clarte_explications' => '/Clarté\s*:?\s*(\d+)\/2\s*([^.\n]*)/i',
            'terminologie_chimique' => '/Terminologie\s*:?\s*(\d+)\/2\s*([^.\n]*)/i',
            'coherence_logique' => '/Cohérence\s*:?\s*(\d+)\/2\s*([^.\n]*)/i',
            'references_sources' => '/Références\s*:?\s*(\d+)\/2\s*([^.\n]*)/i'
        ];

        foreach ($criteriaPatterns as $key => $pattern) {
            if (preg_match($pattern, $commentaire, $matches)) {
                $criteres[$key] = [
                    'score' => (int)$matches[1],
                    'commentaire' => trim($matches[2] ?? '')
                ];
            } else {
                // Fallback avec score par défaut
                $criteres[$key] = [
                    'score' => 0,
                    'commentaire' => ''
                ];
            }
        }

        return $criteres;
    }

    /**
     * Extraction sécurisée des scores
     */
    protected function extractScoreSafe(string $content, string $aiName, array &$result): void
    {
        try {
            $pattern = "/\*\*ÉVALUATION {$aiName}\s*:\*\*.*?Score total\s*:\s*(\d+)\/12.*?Détails\s*:\s*(.+?)(?=\*\*|$)/si";

            if (preg_match($pattern, $content, $matches)) {
                $result['total'] = (int)$matches[1];
                $result['details'] = trim($matches[2]);
            } else {
                // Fallback : chercher juste le score
                if (preg_match("/{$aiName}.*?(\d+)\/12/i", $content, $scoreMatches)) {
                    $result['total'] = (int)$scoreMatches[1];
                }
            }
        } catch (Exception $e) {
            Log::warning("⚠️ Erreur extraction score pour {$aiName}", ['error' => $e->getMessage()]);
        }
    }

    /**
     * Déterminer la meilleure IA par score si parsing échoue
     */
    protected function determineBestAIByScore(array $scores): string
    {
        $maxScore = max($scores['gpt4']['total'], $scores['deepseek']['total'], $scores['qwen']['total']);

        if ($scores['gpt4']['total'] === $maxScore) return 'GPT-4';
        if ($scores['deepseek']['total'] === $maxScore) return 'DeepSeek';
        if ($scores['qwen']['total'] === $maxScore) return 'Qwen';

        return 'Indéterminé';
    }

    /**
     * Normaliser le nom des IA
     */
    protected function normalizeAIName(string $name): string
    {
        $name = strtolower(trim($name));

        if (str_contains($name, 'gpt')) return 'GPT-4';
        if (str_contains($name, 'deepseek')) return 'DeepSeek';
        if (str_contains($name, 'qwen')) return 'Qwen';

        return 'Indéterminé';
    }

    /**
     * Créer un JSON sécurisé pour l'évaluation
     */
    protected function createSafeJsonEvaluation(array $evaluation): string
    {
        $safeEvaluation = [
            'score_total' => $evaluation['total'] ?? 0,
            'commentaire' => $this->sanitizeText($evaluation['comment'] ?? ''),

            // 🔧 AJOUT : Critères détaillés pour l'affichage
            'criteres' => [
                'exactitude_scientifique' => [
                    'score' => $evaluation['exactitude_scientifique'] ?? 0,
                    'commentaire' => $this->sanitizeText($evaluation['exactitude_scientifique_comment'] ?? '')
                ],
                'completude_reponse' => [
                    'score' => $evaluation['completude_reponse'] ?? 0,
                    'commentaire' => $this->sanitizeText($evaluation['completude_reponse_comment'] ?? '')
                ],
                'clarte_explications' => [
                    'score' => $evaluation['clarte_explications'] ?? 0,
                    'commentaire' => $this->sanitizeText($evaluation['clarte_explications_comment'] ?? '')
                ],
                'terminologie_chimique' => [
                    'score' => $evaluation['terminologie_chimique'] ?? 0,
                    'commentaire' => $this->sanitizeText($evaluation['terminologie_chimique_comment'] ?? '')
                ],
                'coherence_logique' => [
                    'score' => $evaluation['coherence_logique'] ?? 0,
                    'commentaire' => $this->sanitizeText($evaluation['coherence_logique_comment'] ?? '')
                ],
                'references_sources' => [
                    'score' => $evaluation['references_sources'] ?? 0,
                    'commentaire' => $this->sanitizeText($evaluation['references_sources_comment'] ?? '')
                ]
            ]
        ];

        return json_encode($safeEvaluation, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Nettoyage sécurisé du texte
     */
    protected function sanitizeText(?string $text): ?string
    {
        if (is_null($text)) return null;

        // Nettoyer l'UTF-8
        $cleaned = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        // Supprimer les caractères de contrôle
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned);

        // Assurer un UTF-8 valide
        if (!mb_check_encoding($cleaned, 'UTF-8')) {
            $cleaned = mb_convert_encoding($cleaned, 'UTF-8', 'auto');
        }

        return trim($cleaned);
    }

    /**
     * Fallback vers l'évaluation standard si la chimie échoue
     */
    protected function fallbackToStandardEvaluation(Question $question): array
    {
        try {
            Log::info('🔄 FALLBACK ÉVALUATION STANDARD POUR CHIMIE', [
                'question_id' => $question->id
            ]);

            // Utiliser le service d'évaluation standard
            $evaluationController = app(\App\Http\Controllers\EvaluationController::class);
            $result = $evaluationController->evaluateQuestion($question);

            // Modifier le type pour indiquer que c'était prévu pour la chimie
            if ($result['success'] && isset($result['evaluation'])) {
                $result['evaluation']->update([
                    'evaluation_type' => 'chemistry_fallback',
                    'commentaire_global' => 'Évaluation standard utilisée (fallback chimie)'
                ]);
            }

            return $result;

        } catch (Exception $e) {
            Log::error('❌ Échec fallback évaluation standard', [
                'question_id' => $question->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Échec évaluation chimie et fallback : ' . $e->getMessage()
            ];
        }
    }

    /**
     * Détection de langue française (réutilisée du code original)
     */
    protected function detectFrenchLanguage(string $text): bool
    {
        $frenchIndicators = [
            'qu\'est-ce', 'quelle', 'quel', 'comment', 'pourquoi', 'où',
            'réaction', 'molécule', 'élément', 'composé', 'équation',
            'chimie', 'est', 'sont', 'dans', 'avec', 'pour'
        ];

        $text_lower = strtolower($text);
        $matches = 0;

        foreach ($frenchIndicators as $indicator) {
            if (str_contains($text_lower, $indicator)) {
                $matches++;
            }
        }

        return $matches >= 2;
    }

    /**
     * Détection question chimie (version simplifiée)
     */
    public function isChemistryQuestion(Question $question): bool
    {
        $content = strtolower($question->content);

        $chemistryKeywords = [
            'chimie', 'chemistry', 'réaction', 'reaction', 'molécule', 'molecule',
            'atome', 'atom', 'élément', 'element', 'composé', 'compound',
            'formule', 'formula', 'équation', 'equation', 'pH'
        ];

        foreach ($chemistryKeywords as $keyword) {
            if (str_contains($content, $keyword)) {
                return true;
            }
        }

        return false;
    }
}
