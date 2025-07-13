<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Wolfram Alpha Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'API Wolfram Alpha utilisée pour les évaluations
    | mathématiques et la validation des réponses IA.
    |
    */

    'app_id' => env('WOLFRAM_APP_ID'),

    'base_url' => env('WOLFRAM_BASE_URL', 'https://api.wolframalpha.com/v2'),

    'timeout' => (int) env('WOLFRAM_TIMEOUT', 30),

    'cache_duration' => (int) env('WOLFRAM_CACHE_DURATION', 3600), // 1 heure

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */

    'endpoints' => [
        'simple' => '/simple',
        'query' => '/query',
        'result' => '/result',
    ],

    /*
    |--------------------------------------------------------------------------
    | Detection Settings
    |--------------------------------------------------------------------------
    |
    | Configuration pour la détection automatique des questions mathématiques
    |
    */

    'detection' => [
        'keywords' => [
            'fr' => [
                'calculer', 'calcul', 'résoudre', 'solution', 'équation',
                'dérivée', 'intégrale', 'limite', 'fonction', 'racine',
                'facteur', 'simplifier', 'développer', 'pourcentage',
                'probabilité', 'statistique', 'géométrie', 'trigonométrie',
                'logarithme', 'exponentielle', 'matrice', 'vecteur',
                'nombre', 'chiffre', 'somme', 'produit', 'quotient'
            ],
            'en' => [
                'calculate', 'solve', 'equation', 'derivative', 'integral',
                'limit', 'function', 'factor', 'simplify', 'expand',
                'percentage', 'probability', 'statistics', 'geometry',
                'trigonometry', 'logarithm', 'exponential', 'matrix',
                'vector', 'number', 'sum', 'product', 'quotient'
            ]
        ],

        'symbols' => [
            '+', '-', '*', '×', '÷', '/', '=', '≠', '≈', '<', '>',
            '≤', '≥', '√', '^', '²', '³', '°', 'π', 'Σ', '∑', '∫',
            '∞', '∆', 'α', 'β', 'γ', 'θ', 'λ', 'μ', 'σ'
        ],

        'patterns' => [
            '/\d+\s*[\+\-\*\/\^]\s*\d+/',  // Opérations numériques
            '/\d+\/\d+/',                   // Fractions
            '/\d+[eE][\+\-]?\d+/',         // Notation scientifique
            '/\(\s*\d+.*?\d+\s*\)/',       // Expressions parenthésées
            '/[a-z]\s*[\+\-\*\/\^]\s*\d+/', // Variables avec opérations
            '/sin|cos|tan|log|ln|exp/',     // Fonctions mathématiques
        ],

        'domains' => [
            'math', 'mathematics', 'mathématiques', 'maths',
            'logic', 'logique', 'calcul', 'calculation',
            'algebra', 'algèbre', 'geometry', 'géométrie'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Processing
    |--------------------------------------------------------------------------
    */

    'processing' => [
        'max_response_length' => 1000,
        'strip_control_chars' => true,
        'trim_whitespace' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Limites de l'API Wolfram Alpha Free Tier
    |
    */

    'rate_limits' => [
        'requests_per_month' => 2000,
        'requests_per_second' => 1,
        'warning_threshold' => 1800, // 90% du quota mensuel
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour les cas où Wolfram Alpha n'est pas disponible
    |
    */

    'fallback' => [
        'enabled' => env('WOLFRAM_FALLBACK_ENABLED', true),
        'continue_without_reference' => true,
        'log_failures' => true,
        'retry_attempts' => 3,
        'retry_delay' => 5, // secondes
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Logging
    |--------------------------------------------------------------------------
    */

    'monitoring' => [
        'log_requests' => env('WOLFRAM_LOG_REQUESTS', false),
        'log_responses' => env('WOLFRAM_LOG_RESPONSES', false),
        'track_usage' => env('WOLFRAM_TRACK_USAGE', true),
        'alert_on_failure' => env('WOLFRAM_ALERT_ON_FAILURE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing Configuration
    |--------------------------------------------------------------------------
    */

    'testing' => [
        'mock_enabled' => env('WOLFRAM_MOCK_ENABLED', false),
        'test_questions' => [
            '2+2',
            'solve x^2 + 3x + 2 = 0',
            'derivative of x^2',
            'integral of 2x',
            'sqrt(16)',
            'sin(pi/2)',
        ],
        'expected_responses' => [
            '2+2' => '4',
            'sqrt(16)' => '4',
            'sin(pi/2)' => '1',
        ]
    ],

];
