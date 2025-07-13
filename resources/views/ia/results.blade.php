{{-- resources/views/ia/results.blade.php - AVEC ÉVALUATION AUTOMATIQUE --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Résultats - ') }} {{ $domain->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Question posée -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-3 flex items-center">
                        💭 Question posée
                        @if($is_mathematics)
                            <span class="ml-2 px-2 py-1 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 text-xs rounded-full">Mathématiques</span>
                        @elseif($is_programming)
                            <span class="ml-2 px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded-full">Programmation</span>
                        @else
                            <span class="ml-2 px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs rounded-full">Général</span>
                        @endif
                    </h3>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <p class="text-gray-800 dark:text-gray-200">{{ $question->content }}</p>
                    </div>
                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Posée le {{ $question->created_at->format('d/m/Y à H:i') }} |
                        Type: {{ $is_mathematics ? 'Mathématiques' : ($is_programming ? 'Programmation' : 'Général') }}
                        @if($is_evaluable)
                            | <span class="text-green-600 dark:text-green-400 font-medium">Évaluation automatique activée</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Section d'évaluation automatique pour questions évaluables -->
            @if($is_evaluable)
                <div class="bg-gradient-to-r {{ $is_mathematics ? 'from-purple-500 to-indigo-500' : 'from-blue-500 to-purple-500' }} text-white rounded-lg p-6 mb-8" id="evaluation-section">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold mb-2 flex items-center">
                                <span class="mr-2">{{ $is_mathematics ? '🧮' : '🤖' }}</span>
                                {{ $is_mathematics ? 'Évaluation Mathématique' : 'Évaluation de Programmation' }}
                            </h3>
                            <p class="text-blue-100 text-sm mb-2">
                                @if($is_mathematics)
                                    Analyse automatique avec référence Wolfram Alpha
                                @else
                                    Analyse automatique de la qualité des réponses de code
                                @endif
                            </p>
                        </div>

                        <div class="text-right">
                            <!-- Status de l'évaluation -->
                            <div id="evaluation-status" class="mb-3">
                                <div class="flex items-center justify-end">
                                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2" id="status-spinner"></div>
                                    <span id="status-text" class="text-sm">Évaluation en cours...</span>
                                </div>
                            </div>

                            <!-- Bouton pour voir les détails (caché par défaut) -->
                            <a href="{{ route('questions.evaluation.show', $question) }}"
                               id="view-details-btn"
                               class="hidden bg-white bg-opacity-20 hover:bg-opacity-30 text-blue-600 px-6 py-2 rounded-lg transition-colors inline-flex items-center">
                                📊 Voir les détails de l'évaluation
                            </a>
                        </div>
                    </div>

                    <!-- Résumé rapide (caché par défaut) -->
                    <div id="evaluation-summary" class="hidden mt-4 p-4 bg-blue-950 bg-opacity-10 rounded-lg">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                            <div>
                                <div class="text-lg font-bold" id="summary-best-ai">-</div>
                                <div class="text-xs opacity-80">Meilleure IA</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold" id="summary-gpt4-score">-</div>
                                <div class="text-xs opacity-80">GPT-4</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold" id="summary-deepseek-score">-</div>
                                <div class="text-xs opacity-80">DeepSeek</div>
                            </div>
                            <div>
                                <div class="text-lg font-bold" id="summary-qwen-score">-</div>
                                <div class="text-xs opacity-80">Qwen</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Réponses des IA -->
            <div class="space-y-8">
                @foreach($responses as $response)
                    @php
                        $modelNames = [
                            'openai/gpt-4o' => ['name' => 'GPT-4 Omni', 'color' => 'green', 'icon' => 'G4'],
                            'deepseek/deepseek-r1' => ['name' => 'DeepSeek R1', 'color' => 'purple', 'icon' => 'DS'],
                            'qwen/qwen-2.5-72b-instruct' => ['name' => 'Qwen 2.5 72B', 'color' => 'orange', 'icon' => 'QW']
                        ];
                        $model = $modelNames[$response->model_name] ?? ['name' => $response->model_name, 'color' => 'gray', 'icon' => '??'];
                    @endphp

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <!-- En-tête de la réponse -->
                        <div class="bg-{{ $model['color'] }}-500 text-white p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center font-bold text-sm">
                                        {{ $model['icon'] }}
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-lg">{{ $model['name'] }}</h4>
                                        <p class="text-{{ $model['color'] }}-100 text-sm">
                                            Modèle d'intelligence artificielle
                                            @if($is_mathematics)
                                                - Spécialisé en calcul et logique
                                            @elseif($is_programming)
                                                - Spécialisé en programmation
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($response->response_time)
                                        <div class="text-{{ $model['color'] }}-100 text-sm">
                                            ⏱️ {{ number_format($response->response_time, 2) }}s
                                        </div>
                                    @endif
                                    @if($response->token_usage)
                                        <div class="text-{{ $model['color'] }}-100 text-xs">
                                            🔤 {{ number_format($response->token_usage) }} tokens
                                        </div>
                                    @endif

                                    <!-- Score de l'évaluation (si disponible) -->
                                    <div class="evaluation-score hidden mt-1">
                                        <div class="bg-white bg-opacity-20 rounded px-2 py-1">
                                            <span class="text-xs font-bold">Score: <span class="score-value">-</span>/10</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contenu de la réponse -->
                        <div class="p-6">
                            <div class="prose dark:prose-invert max-w-none">
                                @if($response->cleaned_response)
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 font-mono text-sm overflow-x-auto">
                                        {!! nl2br(e($response->cleaned_response)) !!}
                                    </div>
                                @else
                                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 font-mono text-sm overflow-x-auto">
                                        {!! nl2br(e($response->response)) !!}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Navigation -->
            <div class="flex justify-between items-center mt-8">
                <a href="{{ route('ia.index') }}"
                   class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                    ← Retour aux domaines
                </a>

                <a href="{{ route('ia.form', $domain) }}"
                   class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                    ➕ Nouvelle question
                </a>
            </div>
        </div>
    </div>

    <style>
        /* Styles pour les réponses */
        .prose pre {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 0.75rem;
            overflow-x: auto;
        }

        .dark .prose pre {
            background-color: #374151;
            border-color: #4b5563;
            color: #f9fafb;
        }

        /* Animation pour les cartes de réponses */
        .bg-white.dark\:bg-gray-800 {
            animation: fadeInUp 0.6s ease-out;
        }

        .bg-white.dark\:bg-gray-800:nth-child(1) { animation-delay: 0.1s; }
        .bg-white.dark\:bg-gray-800:nth-child(2) { animation-delay: 0.2s; }
        .bg-white.dark\:bg-gray-800:nth-child(3) { animation-delay: 0.3s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Amélioration des couleurs pour les modèles */
        .bg-green-500 { background-color: #10b981; }
        .bg-purple-500 { background-color: #8b5cf6; }
        .bg-orange-500 { background-color: #f59e0b; }

        /* Effet hover sur les cartes */
        .bg-white.dark\:bg-gray-800:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        /* Animation pour le spinner */
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        /* Style pour le badge de score */
        .evaluation-score {
            transition: all 0.3s ease;
        }

        .evaluation-score.show {
            display: block !important;
        }
    </style>

    <script>
        const questionId = {{ $question->id }};
        const isEvaluable = {{ $is_evaluable ? 'true' : 'false' }};

        // Démarrer la vérification de l'évaluation si la question est évaluable
        if (isEvaluable) {
            console.log('🔍 Question évaluable, vérification de l\'évaluation...');
            checkEvaluationStatus();
        }

        function checkEvaluationStatus() {
            fetch(`/questions/${questionId}/evaluation/status`)
                .then(response => response.json())
                .then(data => {
                    console.log('📊 Statut évaluation:', data);

                    if (data.success) {
                        if (data.has_evaluation && data.evaluation) {
                            // Évaluation disponible
                            showEvaluationComplete(data.evaluation);
                        } else if (data.can_evaluate) {
                            // Peut être évaluée, démarrer l'évaluation
                            console.log('🚀 Démarrage de l\'évaluation automatique...');
                            triggerAutomaticEvaluation();
                        } else {
                            // Pas encore prête
                            showEvaluationPending();
                        }
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur vérification statut:', error);
                    showEvaluationError();
                });
        }

        function triggerAutomaticEvaluation() {
            // Déclencher l'évaluation en arrière-plan
            fetch(`/questions/${questionId}/evaluate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    console.log('✅ Évaluation déclenchée:', data);

                    if (data.success) {
                        // Attendre un peu puis vérifier le résultat
                        setTimeout(() => {
                            checkEvaluationStatus();
                        }, 2000);
                    } else {
                        console.error('❌ Erreur évaluation:', data.message);
                        showEvaluationError();
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur déclenchement évaluation:', error);
                    showEvaluationError();
                });
        }

        function showEvaluationComplete(evaluation) {
            console.log('✅ Évaluation complète, affichage des résultats');

            const statusSpinner = document.getElementById('status-spinner');
            const statusText = document.getElementById('status-text');
            const viewDetailsBtn = document.getElementById('view-details-btn');
            const evaluationSummary = document.getElementById('evaluation-summary');

            // Masquer le spinner
            if (statusSpinner) statusSpinner.style.display = 'none';

            // Mettre à jour le texte
            if (statusText) statusText.textContent = 'Évaluation terminée';

            // Afficher le bouton de détails
            if (viewDetailsBtn) viewDetailsBtn.classList.remove('hidden');

            // Afficher le résumé s'il y a des données
            if (evaluationSummary && evaluation) {
                evaluationSummary.classList.remove('hidden');

                // Remplir les données du résumé
                updateSummaryData(evaluation);
            }

            // Afficher les scores sur les cartes de réponses
            updateResponseScores(evaluation);
        }

        function updateSummaryData(evaluation) {
            const bestAiNames = {
                'gpt4': 'GPT-4',
                'deepseek': 'DeepSeek',
                'qwen': 'Qwen'
            };

            document.getElementById('summary-best-ai').textContent =
                bestAiNames[evaluation.best_ai] || evaluation.best_ai || '-';
            document.getElementById('summary-gpt4-score').textContent =
                evaluation.gpt4_score ? evaluation.gpt4_score + '/10' : '-';
            document.getElementById('summary-deepseek-score').textContent =
                evaluation.deepseek_score ? evaluation.deepseek_score + '/10' : '-';
            document.getElementById('summary-qwen-score').textContent =
                evaluation.qwen_score ? evaluation.qwen_score + '/10' : '-';
        }

        function updateResponseScores(evaluation) {
            // Mapping des modèles vers les scores
            const scoreMapping = {
                'openai/gpt-4o': evaluation.gpt4_score,
                'deepseek/deepseek-r1': evaluation.deepseek_score,
                'qwen/qwen-2.5-72b-instruct': evaluation.qwen_score
            };

            // Afficher les scores sur chaque carte de réponse
            const responseCards = document.querySelectorAll('.bg-white.dark\\:bg-gray-800');
            responseCards.forEach((card, index) => {
                const scoreElement = card.querySelector('.evaluation-score');
                const scoreValue = card.querySelector('.score-value');

                if (scoreElement && scoreValue) {
                    // Récupérer le modèle depuis les données (vous devrez adapter selon votre structure)
                    const modelNames = [
                        'openai/gpt-4o',
                        'deepseek/deepseek-r1',
                        'qwen/qwen-2.5-72b-instruct'
                    ];

                    const modelName = modelNames[index];
                    const score = scoreMapping[modelName];

                    if (score !== undefined) {
                        scoreValue.textContent = score;
                        scoreElement.classList.remove('hidden');
                        scoreElement.classList.add('show');
                    }
                }
            });
        }

        function showEvaluationPending() {
            const statusText = document.getElementById('status-text');
            if (statusText) statusText.textContent = 'En attente de toutes les réponses IA...';
        }

        function showEvaluationError() {
            const statusSpinner = document.getElementById('status-spinner');
            const statusText = document.getElementById('status-text');

            if (statusSpinner) statusSpinner.style.display = 'none';
            if (statusText) statusText.textContent = 'Erreur d\'évaluation';
        }

        // Vérifier périodiquement si l'évaluation est terminée (pour les cas où elle prend du temps)
        if (isEvaluable) {
            const intervalId = setInterval(() => {
                const viewDetailsBtn = document.getElementById('view-details-btn');
                if (viewDetailsBtn && !viewDetailsBtn.classList.contains('hidden')) {
                    // Évaluation terminée, arrêter la vérification
                    clearInterval(intervalId);
                } else {
                    // Continuer à vérifier
                    checkEvaluationStatus();
                }
            }, 5000); // Vérifier toutes les 5 secondes

            // Arrêter après 2 minutes maximum
            setTimeout(() => {
                clearInterval(intervalId);
            }, 120000);
        }
    </script>
</x-app-layout>
