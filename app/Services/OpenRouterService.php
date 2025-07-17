<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

class OpenRouterService
{
    protected string $baseUrl = 'https://openrouter.ai/api/v1';
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openrouter.key');
    }

    /**
     * Envoyer une requête à un modèle IA via OpenRouter.
     */
    public function envoyerQuestion(string $question, string $model): array
    {
        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name')
            ])->timeout(60)->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => $question],
                ],
            ]);

            $responseTime = microtime(true) - $startTime;

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'response' => $response->json(),
                    'response_time' => $responseTime
                ];
            }

            return [
                'status' => 'error',
                'response' => 'HTTP Error ' . $response->status() . ': ' . $response->body(),
                'response_time' => $responseTime
            ];

        } catch (ConnectionException $e) {
            $responseTime = microtime(true) - $startTime;
            return [
                'status' => 'error',
                'response' => 'Erreur de connexion: ' . $e->getMessage(),
                'response_time' => $responseTime
            ];
        } catch (RequestException $e) {
            $responseTime = microtime(true) - $startTime;
            return [
                'status' => 'error',
                'response' => 'Erreur de requête: ' . $e->getMessage(),
                'response_time' => $responseTime
            ];
        } catch (\Exception $e) {
            $responseTime = microtime(true) - $startTime;
            return [
                'status' => 'error',
                'response' => 'Exception: ' . $e->getMessage(),
                'response_time' => $responseTime
            ];
        }
    }

    /**
     * Interroger plusieurs modèles IA en parallèle ou séquentiellement
     */
    public function queryMultipleModels(array $models, string $prompt): array
    {
        $results = [];

        foreach ($models as $model) {
            $results[$model] = $this->queryModel($model, $prompt);
        }

        return $results;
    }

    /**
     * Version asynchrone pour interroger plusieurs modèles en parallèle - CORRIGÉE
     */
    public function queryMultipleModelsAsync(array $models, string $prompt): array
    {
        $results = [];
        $startTimes = [];

        // Traiter chaque modèle séquentiellement pour éviter les problèmes d'async
        foreach ($models as $model) {
            $startTimes[$model] = microtime(true);

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => config('app.name')
                ])->timeout(120)->post($this->baseUrl . '/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 4000,
                ]);

                $responseTime = microtime(true) - $startTimes[$model];

                if ($response->successful()) {
                    $results[$model] = [
                        'status' => 'success',
                        'response' => $response->json(),
                        'response_time' => $responseTime
                    ];
                } else {
                    $results[$model] = [
                        'status' => 'error',
                        'response' => 'HTTP Error ' . $response->status() . ': ' . $response->body(),
                        'response_time' => $responseTime
                    ];
                }

            } catch (ConnectionException $e) {
                $responseTime = microtime(true) - $startTimes[$model];
                $results[$model] = [
                    'status' => 'error',
                    'response' => 'Erreur de connexion: ' . $e->getMessage(),
                    'response_time' => $responseTime
                ];
            } catch (RequestException $e) {
                $responseTime = microtime(true) - $startTimes[$model];
                $results[$model] = [
                    'status' => 'error',
                    'response' => 'Erreur de requête: ' . $e->getMessage(),
                    'response_time' => $responseTime
                ];
            } catch (\Exception $e) {
                $responseTime = microtime(true) - $startTimes[$model];
                $results[$model] = [
                    'status' => 'error',
                    'response' => 'Exception: ' . $e->getMessage(),
                    'response_time' => $responseTime
                ];

                Log::error("Erreur pour le modèle {$model}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return $results;
    }

    /**
     * Tester la connexion à OpenRouter - CORRIGÉE
     */
    public function testConnection(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(30)->get($this->baseUrl . '/models');

            if ($response->successful()) {
                return [
                    'status' => 'success',
                    'message' => 'Connexion réussie à OpenRouter'
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Erreur HTTP: ' . $response->status() . ' - ' . $response->body()
            ];

        } catch (ConnectionException $e) {
            return [
                'status' => 'error',
                'message' => 'Erreur de connexion: ' . $e->getMessage()
            ];
        } catch (RequestException $e) {
            return [
                'status' => 'error',
                'message' => 'Erreur de requête: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Interroge un seul modèle IA via OpenRouter - CORRIGÉE
     */
    public function queryModel(string $model, string $prompt): array
    {
        $startTime = microtime(true);
        $response = null;

        try {
            Log::info("Envoi requête à OpenRouter", [
                'model' => $model,
                'prompt_length' => strlen($prompt)
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name')
            ])->timeout(120)->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7,
                'max_tokens' => 4000,
                'stream' => false
            ]);

            $responseTime = round((microtime(true) - $startTime), 3);

            if ($response->successful()) {
                $data = $response->json();

                Log::info("Réponse OpenRouter reçue", [
                    'model' => $model,
                    'status' => 'success',
                    'response_time' => $responseTime,
                    'tokens' => $data['usage']['total_tokens'] ?? 'N/A'
                ]);

                return [
                    'status' => 'success',
                    'response' => $data,
                    'response_time' => $responseTime
                ];
            } else {
                $errorBody = $response->body();
                $responseTime = round((microtime(true) - $startTime), 3);

                try {
                    $errorJson = $response->json();
                    $error = $errorJson['error']['message'] ?? $errorBody;
                } catch (\Exception $e) {
                    $error = $errorBody;
                }

                Log::error('Erreur HTTP OpenRouter pour modèle ' . $model, [
                    'status' => $response->status(),
                    'error' => $error,
                    'response_time' => $responseTime,
                    'full_response' => substr($errorBody, 0, 500)
                ]);

                return [
                    'status' => 'error',
                    'response' => "HTTP {$response->status()}: {$error}",
                    'response_time' => $responseTime
                ];
            }

        } catch (ConnectionException $e) {
            $responseTime = round((microtime(true) - $startTime), 3);

            Log::error('Erreur de connexion OpenRouter pour modèle ' . $model, [
                'error' => $e->getMessage(),
                'response_time' => $responseTime
            ]);

            return [
                'status' => 'error',
                'response' => 'Erreur de connexion: ' . $e->getMessage(),
                'response_time' => $responseTime
            ];

        } catch (RequestException $e) {
            $responseTime = round((microtime(true) - $startTime), 3);

            Log::error('Erreur de requête OpenRouter pour modèle ' . $model, [
                'error' => $e->getMessage(),
                'response_time' => $responseTime
            ]);

            return [
                'status' => 'error',
                'response' => 'Erreur de requête: ' . $e->getMessage(),
                'response_time' => $responseTime
            ];

        } catch (\Exception $e) {
            $responseTime = round((microtime(true) - $startTime), 3);

            Log::error('Exception OpenRouter pour modèle ' . $model, [
                'error' => $e->getMessage(),
                'response_time' => $responseTime,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'error',
                'response' => 'Exception: ' . $e->getMessage(),
                'response_time' => $responseTime
            ];
        }
    }

    /**
     * Méthode helper pour valider la configuration
     */
    public function validateConfiguration(): array
    {
        $issues = [];

        if (empty($this->apiKey)) {
            $issues[] = 'Clé API OpenRouter manquante dans la configuration';
        }

        if (!filter_var($this->baseUrl, FILTER_VALIDATE_URL)) {
            $issues[] = 'URL de base OpenRouter invalide';
        }

        if (empty(config('app.url'))) {
            $issues[] = 'URL de l\'application manquante (nécessaire pour HTTP-Referer)';
        }

        if (empty(config('app.name'))) {
            $issues[] = 'Nom de l\'application manquant (nécessaire pour X-Title)';
        }

        return [
            'valid' => empty($issues),
            'issues' => $issues
        ];
    }

    /**
     * Obtient la liste des modèles disponibles - CORRIGÉE
     */
    public function getAvailableModels(): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])->timeout(30)->get($this->baseUrl . '/models');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => 'success',
                    'models' => $data['data'] ?? []
                ];
            }

            return [
                'status' => 'error',
                'message' => 'Erreur HTTP: ' . $response->status(),
                'models' => []
            ];

        } catch (ConnectionException $e) {
            return [
                'status' => 'error',
                'message' => 'Erreur de connexion: ' . $e->getMessage(),
                'models' => []
            ];
        } catch (RequestException $e) {
            return [
                'status' => 'error',
                'message' => 'Erreur de requête: ' . $e->getMessage(),
                'models' => []
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Exception: ' . $e->getMessage(),
                'models' => []
            ];
        }
    }







// === À AJOUTER dans app/Services/OpenRouterService.php ===
// Ajoutez cette méthode dans la classe OpenRouterService

    /**
     * Méthode sendMessage pour la compatibilité avec ChimieEvaluationService
     * Alias de queryModel avec format de réponse standardisé
     */
    public function sendMessage(string $prompt, string $model): array
    {
        Log::info('🤖 Appel sendMessage OpenRouter', [
            'model' => $model,
            'prompt_length' => strlen($prompt)
        ]);

        try {
            // Utiliser la méthode queryModel existante
            $result = $this->queryModel($model, $prompt);

            if ($result['status'] === 'success') {
                Log::info('✅ SendMessage réussi', [
                    'model' => $model,
                    'response_time' => $result['response_time'] ?? null
                ]);

                return [
                    'success' => true,
                    'response' => $result['response'],
                    'response_time' => $result['response_time'] ?? null
                ];
            } else {
                Log::error('❌ SendMessage échoué', [
                    'model' => $model,
                    'error' => $result['response'] ?? 'Unknown error'
                ]);

                return [
                    'success' => false,
                    'response' => $result['response'] ?? 'Unknown error',
                    'response_time' => $result['response_time'] ?? null
                ];
            }

        } catch (Exception $e) {
            Log::error('💥 Exception sendMessage', [
                'model' => $model,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'response' => 'Exception: ' . $e->getMessage(),
                'response_time' => null
            ];
        }
    }

    /**
     * Alternative: méthode sendMessage avec format Claude spécifique
     */
    public function sendMessageForClaude(string $prompt, string $model = 'anthropic/claude-3.5-sonnet'): array
    {
        Log::info('🧠 Appel Claude via sendMessage', [
            'model' => $model,
            'prompt_length' => strlen($prompt)
        ]);

        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url'),
                'X-Title' => config('app.name')
            ])->timeout(120)->post($this->baseUrl . '/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.3, // Plus faible pour l'évaluation
                'max_tokens' => 6000,  // Plus élevé pour les évaluations détaillées
                'stream' => false
            ]);

            $responseTime = round((microtime(true) - $startTime), 3);

            if ($response->successful()) {
                $data = $response->json();

                Log::info('✅ Claude sendMessage réussi', [
                    'model' => $model,
                    'response_time' => $responseTime,
                    'tokens' => $data['usage']['total_tokens'] ?? 'N/A'
                ]);

                return [
                    'success' => true,
                    'response' => $data,
                    'response_time' => $responseTime
                ];
            } else {
                $errorBody = $response->body();

                try {
                    $errorJson = $response->json();
                    $error = $errorJson['error']['message'] ?? $errorBody;
                } catch (Exception $e) {
                    $error = $errorBody;
                }

                Log::error('❌ Claude sendMessage échoué', [
                    'model' => $model,
                    'status' => $response->status(),
                    'error' => $error,
                    'response_time' => $responseTime
                ]);

                return [
                    'success' => false,
                    'response' => "HTTP {$response->status()}: {$error}",
                    'response_time' => $responseTime
                ];
            }

        } catch (ConnectionException $e) {
            $responseTime = round((microtime(true) - $startTime), 3);

            Log::error('💥 Connexion Claude échouée', [
                'model' => $model,
                'error' => $e->getMessage(),
                'response_time' => $responseTime
            ]);

            return [
                'success' => false,
                'response' => 'Erreur de connexion: ' . $e->getMessage(),
                'response_time' => $responseTime
            ];

        } catch (RequestException $e) {
            $responseTime = round((microtime(true) - $startTime), 3);

            Log::error('💥 Requête Claude échouée', [
                'model' => $model,
                'error' => $e->getMessage(),
                'response_time' => $responseTime
            ]);

            return [
                'success' => false,
                'response' => 'Erreur de requête: ' . $e->getMessage(),
                'response_time' => $responseTime
            ];

        } catch (Exception $e) {
            $responseTime = round((microtime(true) - $startTime), 3);

            Log::error('💥 Exception Claude', [
                'model' => $model,
                'error' => $e->getMessage(),
                'response_time' => $responseTime,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'response' => 'Exception: ' . $e->getMessage(),
                'response_time' => $responseTime
            ];
        }
    }

    /**
     * Méthode utilitaire pour valider un modèle
     */
    public function isValidModel(string $model): bool
    {
        $validModels = [
            'anthropic/claude-3.5-sonnet',
            'anthropic/claude-3-haiku',
            'openai/gpt-4o',
            'openai/gpt-4-turbo',
            'deepseek/deepseek-r1',
            'qwen/qwen-2.5-72b-instruct'
        ];

        return in_array($model, $validModels);
    }

    /**
     * Obtenir la liste des modèles disponibles
     */
}
