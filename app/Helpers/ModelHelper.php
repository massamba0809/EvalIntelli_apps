<?php

namespace App\Helpers;

class ModelHelper
{
    /**
     * Formate le nom du modèle pour l'affichage
     */
    public static function formatModelName(string $model): string
    {
        $modelNames = [
            // Modèles OpenAI
            'openai/gpt-4' => 'ChatGPT-4',
            'openai/gpt-4o' => 'ChatGPT-4 Omni',
            'openai/gpt-4-turbo' => 'ChatGPT-4 Turbo',

            // Modèles DeepSeek (IDs mis à jour)
            'deepseek/deepseek-r1' => 'DeepSeek R1',
            'deepseek/deepseek-v3' => 'DeepSeek V3',
            'deepseek/deepseek-chat' => 'DeepSeek Chat',
            'deepseek-ai/deepseek-chat' => 'DeepSeek Chat (Legacy)', // Ancien ID pour compatibilité

            // Modèles Qwen
            'qwen/qwen-2.5-72b-instruct' => 'Qwen 2.5 72B',
            'qwen/qwen1.5-72b-chat' => 'Qwen 1.5 72B', // Ancien ID pour compatibilité

            // Modèles Anthropic
            'anthropic/claude-3-opus' => 'Claude 3 Opus',
            'anthropic/claude-3-sonnet' => 'Claude 3 Sonnet',
            'anthropic/claude-3.5-sonnet' => 'Claude 3.5 Sonnet',
            'anthropic/claude-3.5-haiku' => 'Claude 3.5 Haiku',

            // Modèles Meta
            'meta-llama/llama-2-70b-chat' => 'Llama 2 70B',
            'meta-llama/llama-3.1-70b-instruct' => 'Llama 3.1 70B',
            'meta-llama/llama-3.2-90b-vision-instruct' => 'Llama 3.2 90B Vision',

            // Modèles Google
            'google/gemini-pro' => 'Gemini Pro',
            'google/gemini-pro-vision' => 'Gemini Pro Vision',

            // Modèles Mistral
            'mistralai/mistral-7b-instruct' => 'Mistral 7B',
            'mistralai/mixtral-8x7b-instruct' => 'Mixtral 8x7B',
        ];

        return $modelNames[$model] ?? self::formatGenericModelName($model);
    }

    /**
     * Formate un nom de modèle générique si non trouvé dans la liste
     */
    private static function formatGenericModelName(string $model): string
    {
        $parts = explode('/', $model);
        $modelPart = end($parts);

        // Remplace les tirets et underscores par des espaces et met en forme
        $formatted = str_replace(['-', '_'], ' ', $modelPart);
        $formatted = ucwords($formatted);

        // Ajoute le provider si disponible
        if (count($parts) > 1) {
            $provider = ucfirst($parts[0]);
            return $provider . ' ' . $formatted;
        }

        return $formatted;
    }

    /**
     * Obtient la couleur associée au modèle
     */
    public static function getModelColor(string $model): string
    {
        $colors = [
            // OpenAI - Vert
            'openai/gpt-4' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'openai/gpt-4o' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'openai/gpt-4-turbo' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',

            // DeepSeek - Bleu
            'deepseek/deepseek-r1' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'deepseek/deepseek-v3' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'deepseek/deepseek-chat' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
            'deepseek-ai/deepseek-chat' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',

            // Qwen - Violet
            'qwen/qwen-2.5-72b-instruct' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
            'qwen/qwen1.5-72b-chat' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',

            // Anthropic - Orange
            'anthropic/claude-3-opus' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
            'anthropic/claude-3-sonnet' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
            'anthropic/claude-3.5-sonnet' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
            'anthropic/claude-3.5-haiku' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',

            // Meta - Rouge
            'meta-llama/llama-2-70b-chat' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'meta-llama/llama-3.1-70b-instruct' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'meta-llama/llama-3.2-90b-vision-instruct' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',

            // Google - Jaune
            'google/gemini-pro' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'google/gemini-pro-vision' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',

            // Mistral - Indigo
            'mistralai/mistral-7b-instruct' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
            'mistralai/mixtral-8x7b-instruct' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
        ];

        return $colors[$model] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }

    /**
     * Obtient des informations détaillées sur un modèle
     */
    public static function getModelInfo(string $model): array
    {
        $modelInfo = [
            'openai/gpt-4o' => [
                'provider' => 'OpenAI',
                'description' => 'Modèle multimodal avancé avec capacités vision et texte',
                'context_length' => '128K tokens',
                'specialties' => ['Raisonnement', 'Code', 'Vision', 'Multimodal']
            ],
            'deepseek/deepseek-r1' => [
                'provider' => 'DeepSeek',
                'description' => 'Modèle de raisonnement open-source avec tokens de raisonnement visibles',
                'context_length' => '164K tokens',
                'specialties' => ['Raisonnement', 'Mathématiques', 'Code', 'Sciences']
            ],
            'deepseek/deepseek-v3' => [
                'provider' => 'DeepSeek',
                'description' => 'Modèle de chat principal avec 685B paramètres',
                'context_length' => '128K tokens',
                'specialties' => ['Chat général', 'Code', 'Analyse']
            ],
            'qwen/qwen-2.5-72b-instruct' => [
                'provider' => 'Alibaba',
                'description' => 'Modèle optimisé pour suivre les instructions',
                'context_length' => '32K tokens',
                'specialties' => ['Instructions', 'Multilangue', 'Code']
            ],
            'anthropic/claude-3.5-sonnet' => [
                'provider' => 'Anthropic',
                'description' => 'Modèle équilibré entre performance et vitesse',
                'context_length' => '200K tokens',
                'specialties' => ['Écriture', 'Analyse', 'Code', 'Sécurité']
            ]
        ];

        return $modelInfo[$model] ?? [
            'provider' => 'Unknown',
            'description' => 'Modèle IA',
            'context_length' => 'Variable',
            'specialties' => ['Usage général']
        ];
    }

    /**
     * Vérifie si un modèle est disponible (pour validation)
     */
    public static function isValidModel(string $model): bool
    {
        $validModels = [
            'openai/gpt-4o',
            'deepseek/deepseek-r1',
            'deepseek/deepseek-v3',
            'qwen/qwen-2.5-72b-instruct',
            'anthropic/claude-3.5-sonnet',
            'anthropic/claude-3.5-haiku',
            'meta-llama/llama-3.1-70b-instruct',
            'google/gemini-pro',
            'mistralai/mixtral-8x7b-instruct'
        ];

        return in_array($model, $validModels);
    }

    /**
     * Obtient la liste des modèles recommandés par catégorie
     */
    public static function getRecommendedModels(): array
    {
        return [
            'coding' => [
                'deepseek/deepseek-r1',
                'openai/gpt-4o',
                'anthropic/claude-3.5-sonnet'
            ],
            'reasoning' => [
                'deepseek/deepseek-r1',
                'openai/gpt-4o',
                'anthropic/claude-3.5-sonnet'
            ],
            'creative' => [
                'anthropic/claude-3.5-sonnet',
                'openai/gpt-4o',
                'deepseek/deepseek-v3'
            ],
            'multilingual' => [
                'qwen/qwen-2.5-72b-instruct',
                'deepseek/deepseek-v3',
                'openai/gpt-4o'
            ],
            'fast_response' => [
                'anthropic/claude-3.5-haiku',
                'deepseek/deepseek-v3',
                'mistralai/mixtral-8x7b-instruct'
            ]
        ];
    }
}
