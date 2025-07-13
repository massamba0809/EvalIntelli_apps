<?php


if (!function_exists('formatModelName')) {
    /**
     * Formate le nom d'un modèle IA pour l'affichage
     */
    function formatModelName(string $model): string
    {
        $modelNames = [
            'openai/gpt-4o' => 'GPT-4 Omni',
            'deepseek/deepseek-r1' => 'DeepSeek R1',
            'deepseek/deepseek-v3' => 'DeepSeek V3',
            'qwen/qwen-2.5-72b-instruct' => 'Qwen 2.5 72B',
            'anthropic/claude-3.5-sonnet' => 'Claude 3.5 Sonnet',
            // Anciens modèles pour compatibilité
            'openai/gpt-4' => 'ChatGPT-4',
            'deepseek/deepseek-coder' => 'DeepSeek Coder',
            'qwen/qwen1.5-72b-chat' => 'Qwen 72B'
        ];

        return $modelNames[$model] ?? ucwords(str_replace(['/', '-'], [' ', ' '], $model));
    }
}

if (!function_exists('getAiKeyFromModel')) {
    /**
     * Convertit le nom complet du modèle en clé courte
     */
    function getAiKeyFromModel(string $modelName): string
    {
        $mapping = [
            'openai/gpt-4o' => 'gpt4',
            'deepseek/deepseek-r1' => 'deepseek',
            'qwen/qwen-2.5-72b-instruct' => 'qwen'
        ];

        return $mapping[$modelName] ?? strtolower(str_replace(['/', '-'], ['_', '_'], $modelName));
    }
}

if (!function_exists('getAiDisplayName')) {
    /**
     * Obtient le nom d'affichage d'une IA à partir de sa clé courte
     */
    function getAiDisplayName(string $aiKey): string
    {
        $names = [
            'gpt4' => 'GPT-4 Omni',
            'deepseek' => 'DeepSeek R1',
            'qwen' => 'Qwen 2.5 72B'
        ];

        return $names[$aiKey] ?? ucfirst($aiKey);
    }
}
