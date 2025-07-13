<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

/**
 * Service DeepL API avec auto-détection du type de clé
 */
class DeepLService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected int $timeout = 30;
    protected bool $debugMode;

    public function __construct()
    {
        $this->apiKey = env('DEEPL_API_KEY');
        $this->debugMode = config('app.debug', false);

        // 🎯 AUTO-DÉTECTION du type de clé et URL appropriée
        $this->baseUrl = $this->determineBaseUrl();

        \Log::info('🔧 DeepL Service Init avec auto-détection', [
            'api_key_preview' => substr($this->apiKey, 0, 8) . '...',
            'key_type' => $this->getKeyType(),
            'base_url' => $this->baseUrl,
            'auto_detected' => true
        ]);
    }

    /**
     * 🔍 Détermine automatiquement l'URL selon le type de clé
     */
    protected function determineBaseUrl(): string
    {
        // Si URL explicitement définie dans .env, l'utiliser
        $explicitUrl = env('DEEPL_BASE_URL');
        if (!empty($explicitUrl)) {
            return $explicitUrl;
        }

        // Auto-détection basée sur la clé
        if ($this->isFreeApiKey($this->apiKey)) {
            return 'https://api-free.deepl.com/v2';
        } else {
            return 'https://api.deepl.com/v2';
        }
    }

    /**
     * 🔑 Détecte si c'est une clé gratuite
     */
    protected function isFreeApiKey(string $apiKey): bool
    {
        return !empty($apiKey) && str_ends_with($apiKey, ':fx');
    }

    /**
     * 📋 Obtient le type de clé
     */
    protected function getKeyType(): string
    {
        if (empty($this->apiKey)) {
            return 'missing';
        }

        return $this->isFreeApiKey($this->apiKey) ? 'free' : 'pro';
    }

    /**
     * 🎯 Interface principale - Traduit un texte
     */
    public function translate(string $text, string $targetLang, string $sourceLang = null): array
    {
        $startTime = microtime(true);

        if (empty(trim($text))) {
            return $this->createErrorResponse('Texte vide', $startTime);
        }

        if (empty($this->apiKey)) {
            return $this->createErrorResponse('Clé API DeepL manquante', $startTime);
        }

        $this->logDebug('🚀 DeepL API - Début traduction', [
            'text_length' => strlen($text),
            'target_lang' => $targetLang,
            'source_lang' => $sourceLang,
            'key_type' => $this->getKeyType(),
            'base_url' => $this->baseUrl
        ]);

        // Cache
        $cacheKey = 'deepl_' . md5($text . '_' . $targetLang . '_' . ($sourceLang ?? 'auto'));

        if (!$this->debugMode && Cache::has($cacheKey)) {
            $this->logDebug('📦 Cache hit DeepL');
            return Cache::get($cacheKey);
        }

        // Exécution
        $result = $this->executeTranslation($text, $targetLang, $sourceLang, $startTime);

        // Cache si succès
        if ($result['status'] === 'success' && !$this->debugMode) {
            Cache::put($cacheKey, $result, 3600);
        }

        return $result;
    }

    /**
     * 🔧 Logique d'exécution principale
     */
    protected function executeTranslation(string $text, string $targetLang, ?string $sourceLang, float $startTime): array
    {
        try {
            $payload = [
                'text' => [$text], // Toujours un tableau
                'target_lang' => strtoupper($targetLang),
            ];

            // Ajouter la langue source si spécifiée
            if ($sourceLang && $sourceLang !== 'auto') {
                $payload['source_lang'] = strtoupper($sourceLang);
            }

            \Log::info('🚀 Payload DeepL avec auto-détection', [
                'payload' => $payload,
                'url' => $this->baseUrl . '/translate',
                'key_type' => $this->getKeyType()
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'DeepL-Auth-Key ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout($this->timeout)->post($this->baseUrl . '/translate', $payload);

            $responseTime = microtime(true) - $startTime;

            \Log::info('📥 Réponse DeepL', [
                'status' => $response->status(),
                'body_preview' => substr($response->body(), 0, 200),
                'response_time' => $responseTime
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['translations']) && count($data['translations']) > 0) {
                    $translation = $data['translations'][0];

                    $this->logDebug('✅ Traduction DeepL réussie', [
                        'detected_source_language' => $translation['detected_source_language'] ?? 'N/A',
                        'translation_length' => strlen($translation['text']),
                        'response_time' => $responseTime,
                        'key_type' => $this->getKeyType()
                    ]);

                    return $this->createSuccessResponse(
                        $translation['text'],
                        $translation['detected_source_language'] ?? $sourceLang,
                        $responseTime,
                        $data
                    );
                }
            }

            $errorBody = $response->body();
            $this->logDebug('❌ Erreur DeepL API', [
                'status' => $response->status(),
                'error' => $errorBody,
                'response_time' => $responseTime,
                'url_used' => $this->baseUrl,
                'key_type' => $this->getKeyType()
            ]);

            return $this->createErrorResponse(
                "Erreur DeepL API {$response->status()}: " . substr($errorBody, 0, 200),
                $startTime
            );

        } catch (Exception $e) {
            $this->logDebug('❌ Exception DeepL', [
                'error' => $e->getMessage(),
                'response_time' => microtime(true) - $startTime,
                'url_used' => $this->baseUrl,
                'key_type' => $this->getKeyType()
            ]);

            return $this->createErrorResponse(
                'Erreur de connexion DeepL: ' . $e->getMessage(),
                $startTime
            );
        }
    }

    /**
     * 🧪 Test de connexion amélioré
     */
    public function testConnection(): array
    {
        if (empty($this->apiKey)) {
            return ['status' => 'error', 'message' => 'Clé API DeepL manquante'];
        }

        try {
            \Log::info('🧪 Test connexion DeepL', [
                'key_type' => $this->getKeyType(),
                'base_url' => $this->baseUrl,
                'auto_detected' => true
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'DeepL-Auth-Key ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->timeout(10)->post($this->baseUrl . '/translate', [
                'text' => ['Hello'],
                'target_lang' => 'FR'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['translations'][0]['text'])) {
                    return [
                        'status' => 'success',
                        'message' => 'Connexion DeepL réussie',
                        'key_type' => $this->getKeyType(),
                        'base_url' => $this->baseUrl,
                        'auto_detected' => true,
                        'translation_test' => $data['translations'][0]['text']
                    ];
                }
            }

            return [
                'status' => 'error',
                'message' => "Erreur HTTP ({$response->status()}): " . $response->body(),
                'key_type' => $this->getKeyType(),
                'base_url' => $this->baseUrl,
                'suggestion' => $this->getSuggestion()
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Erreur connexion: ' . $e->getMessage(),
                'key_type' => $this->getKeyType(),
                'base_url' => $this->baseUrl,
                'suggestion' => $this->getSuggestion()
            ];
        }
    }

    /**
     * 💡 Suggestions en cas de problème
     */
    protected function getSuggestion(): string
    {
        $keyType = $this->getKeyType();

        if ($keyType === 'free') {
            return "Clé gratuite détectée. Vérifiez que l'URL 'api-free.deepl.com' est accessible.";
        } elseif ($keyType === 'pro') {
            return "Clé Pro détectée. Vérifiez que l'URL 'api.deepl.com' est accessible et que votre abonnement est actif.";
        } else {
            return "Clé API manquante ou invalide.";
        }
    }

    /**
     * 🔧 Validation configuration améliorée
     */
    public function validateConfiguration(): array
    {
        $issues = [];
        $keyType = $this->getKeyType();

        if (empty($this->apiKey)) {
            $issues[] = 'Clé API manquante (DEEPL_API_KEY dans .env)';
        } elseif (strlen($this->apiKey) < 20) {
            $issues[] = 'Clé API incorrecte (trop courte)';
        }

        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            $issues[] = 'URL de base DeepL invalide';
        }

        // Vérification cohérence clé/URL
        $expectedUrl = $this->isFreeApiKey($this->apiKey) ?
            'https://api-free.deepl.com/v2' :
            'https://api.deepl.com/v2';

        $urlMismatch = $this->baseUrl !== $expectedUrl;

        return [
            'valid' => empty($issues) && !$urlMismatch,
            'issues' => $issues,
            'warnings' => $urlMismatch ? ["URL ne correspond pas au type de clé. Attendue: {$expectedUrl}"] : [],
            'api_key_preview' => $this->apiKey ? substr($this->apiKey, 0, 8) . '...' : 'Non configuré',
            'key_type' => $keyType,
            'current_url' => $this->baseUrl,
            'expected_url' => $expectedUrl,
            'auto_detected' => true
        ];
    }

    // ... (garder toutes les autres méthodes inchangées)

    /**
     * ✅ Réponse de succès
     */
    protected function createSuccessResponse(string $translatedText, ?string $detectedLang, float $responseTime, array $rawData): array
    {
        return [
            'status' => 'success',
            'translated_text' => trim($translatedText),
            'detected_source_language' => $detectedLang,
            'response_time' => round($responseTime, 3),
            'character_count' => strlen($translatedText),
            'key_type' => $this->getKeyType(),
            'api_url' => $this->baseUrl,
            'raw_data' => $rawData
        ];
    }

    /**
     * ❌ Réponse d'erreur
     */
    protected function createErrorResponse(string $error, float $startTime): array
    {
        return [
            'status' => 'error',
            'error' => $error,
            'response_time' => round(microtime(true) - $startTime, 3),
            'key_type' => $this->getKeyType(),
            'api_url' => $this->baseUrl,
            'suggestion' => $this->getSuggestion()
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
}
