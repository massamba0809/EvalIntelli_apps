<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * Service Wolfram Alpha v2.0
 *
 * Remplace app/Services/WolframAlphaService.php
 */
class WolframAlphaService
{
    protected string $appId;
    protected string $baseUrl = 'https://api.wolframalpha.com/v2';
    protected int $timeout = 15;
    protected bool $debugMode;

    // APIs par ordre de priorité
    protected array $apiEndpoints = [
        'result' => '/result',     // Rapide
        'query' => '/query',       // Complète
        'spoken' => '/spoken'      // Alternative
    ];

    public function __construct()
    {
        $this->appId = config('services.wolfram.app_id');
        $this->debugMode = config('app.debug', false);

        if (empty($this->appId)) {
            Log::warning('🔑 Wolfram Alpha: Clé API manquante');
        }
    }

    /**
     * 🎯 Interface principale - Compatible avec votre code existant
     */
    public function querySimple(string $question): array
    {
        $startTime = microtime(true);
        $originalQuestion = trim($question);

        if (empty($originalQuestion)) {
            return $this->createErrorResponse('Question vide', $startTime);
        }

        if (empty($this->appId)) {
            return $this->createErrorResponse('Clé API manquante', $startTime);
        }

        $this->logDebug('🚀 Wolfram Alpha v2 - Début', [
            'question' => $originalQuestion
        ]);

        // Cache
        $cacheKey = 'wolfram_v2_' . md5(strtolower($originalQuestion));

        if (!$this->debugMode && Cache::has($cacheKey)) {
            $this->logDebug('📦 Cache hit');
            return Cache::get($cacheKey);
        }

        // Exécution
        $result = $this->executeQuery($originalQuestion, $startTime);

        // Cache si succès
        if ($result['status'] === 'success' && !$this->debugMode) {
            Cache::put($cacheKey, $result, 3600);
        }

        return $result;
    }

    /**
     * 🔧 Logique d'exécution principale
     */
    protected function executeQuery(string $question, float $startTime): array
    {
        $questionVariants = $this->prepareQuestionVariants($question);

        $this->logDebug('📝 Variantes préparées', $questionVariants);

        // Tester chaque API avec chaque variante
        foreach ($this->apiEndpoints as $apiName => $endpoint) {
            foreach ($questionVariants as $variantName => $variant) {
                $result = $this->tryApi($apiName, $variant);

                if ($result['success']) {
                    $totalTime = microtime(true) - $startTime;

                    $this->logDebug('✅ Succès', [
                        'api' => $apiName,
                        'variant' => $variantName,
                        'time' => $totalTime
                    ]);

                    return $this->createSuccessResponse(
                        $result['response'],
                        $totalTime,
                        $apiName,
                        $variantName
                    );
                }
            }
        }

        // Tous les essais ont échoué
        $totalTime = microtime(true) - $startTime;
        $this->logDebug('❌ Tous les essais ont échoué', ['time' => $totalTime]);

        return $this->createErrorResponse('Aucune stratégie réussie', $startTime);
    }

    /**
     * 📋 Prépare les variantes de question
     */
    protected function prepareQuestionVariants(string $question): array
    {
        return array_filter(array_unique([
            'original' => $question,
            'english' => $this->translateToEnglish($question),
            'math_notation' => $this->normalizeToMathNotation($question),
            'simplified' => $this->simplifyQuestion($question)
        ]), function($variant) {
            return !empty(trim($variant));
        });
    }

    /**
     * 🌐 Traduction française → anglais
     */
    protected function translateToEnglish(string $question): string
    {
        $question = strtolower(trim($question));

        $translations = [
            // Questions
            'calculer' => 'calculate',
            'calcule' => 'calculate',
            'combien fait' => 'what is',
            'quelle est' => 'what is',
            'résoudre' => 'solve',
            'trouve' => 'find',

            // Opérations
            'plus' => 'plus',
            'moins' => 'minus',
            'fois' => 'times',
            'multiplié par' => 'times',
            'divisé par' => 'divided by',
            'au carré' => 'squared',
            'au cube' => 'cubed',

            // Fonctions
            'racine carrée de' => 'square root of',
            'racine carre de' => 'square root of',
            'sinus de' => 'sin',
            'cosinus de' => 'cos',
            'tangente de' => 'tan',
            'logarithme de' => 'log',
            'logarithme naturel de' => 'ln',
            'dérivée de' => 'derivative of',
            'intégrale de' => 'integral of',

            // Mots de liaison
            'de' => 'of',
            'du' => 'of',
            'des' => 'of',
            'le' => 'the',
            'la' => 'the'
        ];

        foreach ($translations as $french => $english) {
            $question = str_replace($french, $english, $question);
        }

        return trim($question);
    }

    /**
     * 🔢 Normalisation mathématique
     */
    protected function normalizeToMathNotation(string $question): string
    {
        $patterns = [
            '/racine carr[ée]* de (\d+)/i' => 'sqrt($1)',
            '/(\d+) au carré/i' => '$1^2',
            '/(\d+) puissance (\d+)/i' => '$1^$2',
            '/(\d+) sur (\d+)/i' => '$1/$2'
        ];

        foreach ($patterns as $pattern => $replacement) {
            $question = preg_replace($pattern, $replacement, $question);
        }

        return trim($question);
    }

    /**
     * ✂️ Simplification
     */
    protected function simplifyQuestion(string $question): string
    {
        $stopWords = [
            'calculer', 'résoudre', 'trouve', 'peux-tu',
            's\'il te plaît', 'aide-moi à'
        ];

        foreach ($stopWords as $word) {
            $question = str_replace($word, '', $question);
        }

        return trim(preg_replace('/\s+/', ' ', $question));
    }

    /**
     * 🎯 Test d'une API spécifique
     */
    protected function tryApi(string $apiName, string $question): array
    {
        try {
            switch ($apiName) {
                case 'result':
                    return $this->callResultApi($question);
                case 'query':
                    return $this->callQueryApi($question);
                case 'spoken':
                    return $this->callSpokenApi($question);
                default:
                    return ['success' => false, 'error' => "API inconnue: {$apiName}"];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * 🚀 API Result
     */
    protected function callResultApi(string $question): array
    {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/result', [
                'appid' => $this->appId,
                'i' => $question,
                'format' => 'plaintext'
            ]);

        if (!$response->successful()) {
            return ['success' => false, 'error' => "HTTP {$response->status()}"];
        }

        $body = trim($response->body());

        if ($this->isValidWolframResponse($body)) {
            return ['success' => true, 'response' => $body];
        }

        return ['success' => false, 'error' => 'Réponse invalide'];
    }

    /**
     * 📊 API Query
     */
    protected function callQueryApi(string $question): array
    {
        $response = Http::timeout($this->timeout + 5)
            ->get($this->baseUrl . '/query', [
                'appid' => $this->appId,
                'input' => $question,
                'format' => 'plaintext',
                'output' => 'json',
                'scanner' => 'Numeric,Identity,Solve,Simplify'
            ]);

        if (!$response->successful()) {
            return ['success' => false, 'error' => "HTTP {$response->status()}"];
        }

        $data = $response->json();

        if (!isset($data['queryresult']) || !($data['queryresult']['success'] ?? false)) {
            return ['success' => false, 'error' => 'Query non réussie'];
        }

        $text = $this->extractTextFromQueryResult($data['queryresult']);

        if (!empty($text)) {
            return ['success' => true, 'response' => $text];
        }

        return ['success' => false, 'error' => 'Aucun texte extractible'];
    }

    /**
     * 🗣️ API Spoken
     */
    protected function callSpokenApi(string $question): array
    {
        $response = Http::timeout($this->timeout)
            ->get($this->baseUrl . '/spoken', [
                'appid' => $this->appId,
                'i' => $question
            ]);

        if (!$response->successful()) {
            return ['success' => false, 'error' => "HTTP {$response->status()}"];
        }

        $body = trim($response->body());

        if ($this->isValidWolframResponse($body)) {
            return ['success' => true, 'response' => $body];
        }

        return ['success' => false, 'error' => 'Réponse spoken invalide'];
    }

    /**
     * 📤 Extraction de texte des pods
     */
    protected function extractTextFromQueryResult(array $queryResult): string
    {
        if (!isset($queryResult['pods'])) return '';

        $priorityPods = ['Result', 'Solution', 'Value', 'Answer'];

        foreach ($priorityPods as $priority) {
            foreach ($queryResult['pods'] as $pod) {
                $title = $pod['title'] ?? '';
                if (stripos($title, $priority) !== false) {
                    $text = $this->extractTextFromPod($pod);
                    if (!empty($text) && strlen($text) < 500) {
                        return $text;
                    }
                }
            }
        }

        return '';
    }

    /**
     * 📝 Extraction d'un pod
     */
    protected function extractTextFromPod(array $pod): string
    {
        if (!isset($pod['subpods'])) return '';

        $texts = [];
        foreach ($pod['subpods'] as $subpod) {
            $plaintext = $subpod['plaintext'] ?? '';
            if (!empty(trim($plaintext))) {
                $texts[] = trim($plaintext);
            }
        }

        return implode(' | ', $texts);
    }

    /**
     * ✅ Validation de réponse
     */
    protected function isValidWolframResponse(string $response): bool
    {
        if (empty(trim($response))) return false;

        $errorPatterns = [
            'No short answer available',
            'Wolfram|Alpha did not understand',
            'Unable to understand',
            'GIF89a'
        ];

        foreach ($errorPatterns as $pattern) {
            if (stripos($response, $pattern) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * ✅ Réponse de succès
     */
    protected function createSuccessResponse(string $response, float $totalTime, string $apiUsed, string $variantUsed): array
    {
        return [
            'status' => 'success',
            'response' => trim($response),
            'has_reference' => true,
            'response_time' => round($totalTime, 3),
            'api_used' => $apiUsed,
            'variant_used' => $variantUsed
        ];
    }

    /**
     * ❌ Réponse d'erreur
     */
    protected function createErrorResponse(string $error, float $startTime): array
    {
        return [
            'status' => 'error',
            'response' => $error,
            'has_reference' => false,
            'response_time' => round(microtime(true) - $startTime, 3)
        ];
    }

    /**
     * 📝 Log conditionnel
     */
    protected function logDebug(string $message, array $context = []): void
    {
        if ($this->debugMode) {
            Log::info($message, $context);
        }
    }

    // ============================
    // MÉTHODES PUBLIQUES (compatibilité)
    // ============================

    /**
     * 🧪 Test de connexion
     */
    public function testConnection(): array
    {
        if (empty($this->appId)) {
            return ['status' => 'error', 'message' => 'Clé API manquante'];
        }

        $result = $this->querySimple('2+2');

        return [
            'status' => $result['status'],
            'message' => $result['status'] === 'success' ?
                'Connexion réussie' : 'Connexion échouée'
        ];
    }

    /**
     * 🔧 Validation configuration
     */
    public function validateConfiguration(): array
    {
        $issues = [];

        if (empty($this->appId)) {
            $issues[] = 'Clé API manquante (WOLFRAM_APP_ID dans .env)';
        } elseif (strlen($this->appId) < 10) {
            $issues[] = 'Clé API incorrecte (trop courte)';
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues,
            'app_id_preview' => $this->appId ? substr($this->appId, 0, 8) . '...' : 'Non configuré'
        ];
    }

    /**
     * 🧮 Détection question mathématique
     */
    public function isMathematicalQuestion(string $question): bool
    {
        $mathIndicators = [
            'calculer', 'résoudre', 'racine', 'dérivée', 'solve',
            '+', '-', '*', '/', '=', '^', 'sqrt', 'sin', 'cos'
        ];

        $questionLower = strtolower($question);

        foreach ($mathIndicators as $indicator) {
            if (str_contains($questionLower, $indicator)) {
                return true;
            }
        }

        return preg_match('/\d+\s*[\+\-\*\/\^]\s*\d+/', $question) ||
            preg_match('/\w+\(\s*\w*\s*\)/', $question);
    }

    /**
     * 📄 Format pour évaluation
     */
    public function formatResponseForEvaluation(string $response): string
    {
        $cleaned = trim($response);

        if (strlen($cleaned) > 1000) {
            $cleaned = substr($cleaned, 0, 1000) . '...';
        }

        return $cleaned;
    }

    /**
     * 🔍 Debug complet
     */
    public function debugQuestion(string $question): array
    {
        $originalDebugMode = $this->debugMode;
        $this->debugMode = true;

        $variants = $this->prepareQuestionVariants($question);
        $results = [];

        foreach ($this->apiEndpoints as $apiName => $endpoint) {
            foreach ($variants as $variantName => $variant) {
                $results["{$apiName}_{$variantName}"] = $this->tryApi($apiName, $variant);
            }
        }

        $this->debugMode = $originalDebugMode;

        return [
            'original_question' => $question,
            'variants' => $variants,
            'is_mathematical' => $this->isMathematicalQuestion($question),
            'configuration' => $this->validateConfiguration(),
            'api_results' => $results
        ];
    }
}
