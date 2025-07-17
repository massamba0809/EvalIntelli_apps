{{-- resources/views/ia/results.blade.php - DESIGN MODERNE AVEC RÉPONSES SUPERPOSÉES --}}
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

            <!-- Section d'évaluation automatique -->
            @if($is_evaluable)
                <div class="bg-gradient-to-r {{ $is_mathematics ? 'from-purple-500 to-indigo-500' : 'from-blue-500 to-purple-500' }} text-white rounded-lg p-6 mb-8" id="evaluation-section">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold mb-2 flex items-center">
                                <span class="mr-2">{{ $is_mathematics ? '🧮' : '🤖' }}</span>
                                {{ $is_mathematics ? 'Évaluation Mathématique' : 'Évaluation de Programmation' }}
                            </h3>
                            <p class="text-blue-100">
                                {{ $is_mathematics ? 'Analyse automatique avec référence Wolfram Alpha' : 'Évaluation automatique des bonnes pratiques de code' }}
                            </p>
                        </div>
                        <div id="status-spinner" class="animate-spin text-3xl">⏳</div>
                    </div>
                    <div class="mt-4">
                        <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-4">
                            <p id="status-text" class="text-blue-800 dark:text-blue-200 font-medium">
                                🔄 Vérification de l'état de l'évaluation...
                            </p>
                            <div class="mt-2">
                                <div class="bg-blue-200 dark:bg-blue-800 rounded-full h-2">
                                    <div id="progress-bar" class="bg-blue-600 dark:bg-blue-400 h-2 rounded-full w-0 transition-all duration-500"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Navigation des onglets IA -->
            <div class="mb-6">
                <div class="flex space-x-1 bg-gray-100 dark:bg-gray-800 p-1 rounded-xl">
                    @foreach($responses as $index => $response)
                        <button onclick="switchTab({{ $index }})"
                                class="ai-tab flex-1 px-4 py-3 rounded-lg font-medium transition-all duration-300 text-sm
                                       {{ $index === 0 ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                                data-tab="{{ $index }}">
                            @if($response->model_name === 'openai/gpt-4o')
                                <span class="text-green-500 mr-2">🤖</span>
                                <span class="font-bold">GPT-4</span>
                            @elseif($response->model_name === 'deepseek/deepseek-r1')
                                <span class="text-blue-500 mr-2">🔍</span>
                                <span class="font-bold">DeepSeek</span>
                            @elseif($response->model_name === 'qwen/qwen-2.5-72b-instruct')
                                <span class="text-purple-500 mr-2">⚡</span>
                                <span class="font-bold">Qwen</span>
                            @else
                                <span class="text-gray-500 mr-2">🤖</span>
                                <span class="font-bold">{{ $response->model_name }}</span>
                            @endif
                            <div class="text-xs mt-1 opacity-75">
                                @if($response->response_time)
                                    ⏱️ {{ number_format($response->response_time, 1) }}s
                                @endif
                                @if($response->token_usage)
                                    | 🔤 {{ number_format($response->token_usage) }}
                                @endif
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Contenu des réponses superposées -->
            <div class="relative">
                @foreach($responses as $index => $response)
                    <div class="ai-content {{ $index === 0 ? 'active' : 'hidden' }}" data-content="{{ $index }}">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                            <!-- Header avec métadonnées -->
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 p-4 border-b border-gray-200 dark:border-gray-600">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center">
                                            @if($response->model_name === 'openai/gpt-4o')
                                                <span class="text-green-500 mr-2 text-2xl">🤖</span>
                                                <span class="bg-gradient-to-r from-green-600 to-green-500 bg-clip-text text-transparent">GPT-4 Turbo</span>
                                            @elseif($response->model_name === 'deepseek/deepseek-r1')
                                                <span class="text-blue-500 mr-2 text-2xl">🔍</span>
                                                <span class="bg-gradient-to-r from-blue-600 to-blue-500 bg-clip-text text-transparent">DeepSeek R1</span>
                                            @elseif($response->model_name === 'qwen/qwen-2.5-72b-instruct')
                                                <span class="text-purple-500 mr-2 text-2xl">⚡</span>
                                                <span class="bg-gradient-to-r from-purple-600 to-purple-500 bg-clip-text text-transparent">Qwen 2.5</span>
                                            @endif
                                        </h3>
                                        <div class="flex items-center space-x-4 mt-1">
                                            @if($response->response_time)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    ⏱️ {{ number_format($response->response_time, 2) }}s
                                                </span>
                                            @endif
                                            @if($response->token_usage)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    🔤 {{ number_format($response->token_usage) }} tokens
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button onclick="copyFullResponse({{ $index }})"
                                                class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors duration-200 flex items-center">
                                            📋 Copier tout
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Contenu avec séparation claire -->
                            <div class="p-6">
                                @if($is_programming && str_contains($response->cleaned_response, '```'))
                                    @php
                                        $parts = preg_split('/```(\w+)?\n(.*?)```/s', $response->cleaned_response, -1, PREG_SPLIT_DELIM_CAPTURE);
                                    @endphp

                                    <div class="space-y-6">
                                        @for($i = 0; $i < count($parts); $i++)
                                            @if($i % 3 === 0 && trim($parts[$i]))
                                                <!-- Section Explication -->
                                                <div class="explanation-section">
                                                    <div class="flex items-center mb-3">
                                                        <div class="flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full mr-3">
                                                            <span class="text-blue-600 dark:text-blue-400 text-sm font-bold">📝</span>
                                                        </div>
                                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Explication</h4>
                                                    </div>
                                                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl p-4 border-l-4 border-blue-500">
                                                        @php
                                                            $explanation = trim($parts[$i]);
                                                            $isLongExplanation = strlen($explanation) > 400;
                                                            $shortExplanation = $isLongExplanation ? substr($explanation, 0, 400) . '...' : $explanation;
                                                        @endphp
                                                        <div class="prose dark:prose-invert max-w-none">
                                                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed" id="explanation-{{ $index }}-{{ $i }}">
                                                                {!! nl2br(e($shortExplanation)) !!}
                                                            </p>
                                                            @if($isLongExplanation)
                                                                <button onclick="toggleExplanation({{ $index }}, {{ $i }})"
                                                                        class="mt-2 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 text-sm font-medium">
                                                                    📖 Lire la suite
                                                                </button>
                                                                <div class="hidden" id="full-explanation-{{ $index }}-{{ $i }}">
                                                                    {!! nl2br(e($explanation)) !!}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @elseif($i % 3 === 2)
                                                <!-- Section Code -->
                                                <div class="code-section">
                                                    <div class="flex items-center justify-between mb-3">
                                                        <div class="flex items-center">
                                                            <div class="flex items-center justify-center w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full mr-3">
                                                                <span class="text-green-600 dark:text-green-400 text-sm font-bold">💻</span>
                                                            </div>
                                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Code</h4>
                                                            @if(isset($parts[$i-1]) && $parts[$i-1])
                                                                <span class="ml-2 px-2 py-1 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-xs rounded-full">
                                                                    {{ strtoupper($parts[$i-1]) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <button onclick="copyCode({{ $index }}, {{ $i }})"
                                                                class="px-3 py-1 bg-gray-800 hover:bg-gray-700 text-white rounded-lg text-sm font-medium transition-colors duration-200">
                                                            📋 Copier code
                                                        </button>
                                                    </div>
                                                    <div class="relative group">
                                                        <pre class="bg-gray-900 text-green-400 rounded-xl p-4 overflow-x-auto border border-gray-700 shadow-inner code-block" id="code-{{ $index }}-{{ $i }}"><code class="text-sm font-mono">{{ trim($parts[$i]) }}</code></pre>
                                                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                                            <div class="bg-gray-800 text-white px-2 py-1 rounded text-xs">
                                                                {{ str_word_count(trim($parts[$i])) }} mots
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endfor
                                    </div>
                                @else
                                    <!-- Réponse simple avec limitation -->
                                    <div class="simple-response">
                                        <div class="flex items-center mb-4">
                                            <div class="flex items-center justify-center w-8 h-8 bg-gray-100 dark:bg-gray-700 rounded-full mr-3">
                                                <span class="text-gray-600 dark:text-gray-400 text-sm font-bold">💬</span>
                                            </div>
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Réponse</h4>
                                        </div>
                                        <div class="bg-gradient-to-r from-gray-50 to-slate-50 dark:from-gray-800 dark:to-slate-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                                            @php
                                                $cleanResponse = $response->cleaned_response;
                                                $isLongResponse = strlen($cleanResponse) > 600;
                                                $shortResponse = $isLongResponse ? substr($cleanResponse, 0, 600) . '...' : $cleanResponse;
                                            @endphp
                                            <div class="prose dark:prose-invert max-w-none">
                                                <p class="text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-line" id="response-{{ $index }}">{{ $shortResponse }}</p>
                                                @if($isLongResponse)
                                                    <button onclick="toggleResponse({{ $index }})"
                                                            class="mt-4 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-colors duration-200">
                                                        📖 Voir la réponse complète
                                                    </button>
                                                    <div class="hidden mt-4" id="full-response-{{ $index }}">
                                                        <p class="text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-line">{{ $cleanResponse }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Boutons d'action -->
            <div class="mt-8 flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0 sm:space-x-4">
                <a href="{{ route('ia.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg transition-colors font-medium">
                    ⬅️ Retour à l'accueil
                </a>

                @if($is_evaluable)
                    <a href="{{ route('questions.evaluation.show', $question) }}" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white px-6 py-3 rounded-lg transition-all duration-200 font-medium shadow-lg hover:shadow-xl">
                        📊 Voir l'évaluation détaillée
                    </a>
                @endif
            </div>
        </div>
    </div>

    <style>
        .ai-tab.active {
            @apply bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm;
        }

        .ai-content {
            transition: all 0.3s ease-in-out;
        }

        .ai-content.hidden {
            opacity: 0;
            transform: translateY(10px);
        }

        .ai-content.active {
            opacity: 1;
            transform: translateY(0);
        }

        .code-block {
            font-family: 'Fira Code', 'Monaco', 'Menlo', monospace;
            line-height: 1.5;
        }

        .explanation-section {
            animation: fadeInUp 0.5s ease-out;
        }

        .code-section {
            animation: fadeInUp 0.5s ease-out 0.1s both;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .simple-response {
            animation: fadeInUp 0.5s ease-out;
        }
    </style>

    <script>
        // === VARIABLES GLOBALES ===
        const questionId = {{ $question->id }};
        const isEvaluable = {{ $is_evaluable ? 'true' : 'false' }};

        // Protection contre la boucle infinie
        let evaluationInProgress = false;
        let maxAttempts = 3;

        // === SYSTÈME D'ONGLETS ===
        function switchTab(tabIndex) {
            // Mettre à jour les onglets
            document.querySelectorAll('.ai-tab').forEach((tab, index) => {
                if (index === tabIndex) {
                    tab.classList.add('bg-white', 'dark:bg-gray-700', 'text-gray-900', 'dark:text-white', 'shadow-sm');
                    tab.classList.remove('text-gray-600', 'dark:text-gray-400');
                } else {
                    tab.classList.remove('bg-white', 'dark:bg-gray-700', 'text-gray-900', 'dark:text-white', 'shadow-sm');
                    tab.classList.add('text-gray-600', 'dark:text-gray-400');
                }
            });

            // Mettre à jour le contenu
            document.querySelectorAll('.ai-content').forEach((content, index) => {
                if (index === tabIndex) {
                    content.classList.remove('hidden');
                    content.classList.add('active');
                } else {
                    content.classList.add('hidden');
                    content.classList.remove('active');
                }
            });
        }

        // === FONCTIONS DE COPIE ===
        function copyCode(responseIndex, partIndex) {
            const codeElement = document.getElementById(`code-${responseIndex}-${partIndex}`);
            const text = codeElement.querySelector('code').textContent;

            navigator.clipboard.writeText(text).then(() => {
                showCopySuccess(`Code copié !`);
            }).catch(err => {
                console.error('Erreur de copie:', err);
                showCopyError();
            });
        }

        function copyFullResponse(responseIndex) {
            const contentElement = document.querySelector(`[data-content="${responseIndex}"]`);
            const text = contentElement.textContent;

            navigator.clipboard.writeText(text).then(() => {
                showCopySuccess(`Réponse complète copiée !`);
            }).catch(err => {
                console.error('Erreur de copie:', err);
                showCopyError();
            });
        }

        function showCopySuccess(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 2000);
        }

        function showCopyError() {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50';
            notification.textContent = 'Erreur de copie';
            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 2000);
        }

        // === FONCTIONS D'EXPANSION ===
        function toggleExplanation(responseIndex, partIndex) {
            const shortElement = document.getElementById(`explanation-${responseIndex}-${partIndex}`);
            const fullElement = document.getElementById(`full-explanation-${responseIndex}-${partIndex}`);
            const button = event.target;

            if (fullElement.classList.contains('hidden')) {
                fullElement.classList.remove('hidden');
                shortElement.style.display = 'none';
                button.textContent = '📖 Voir moins';
            } else {
                fullElement.classList.add('hidden');
                shortElement.style.display = 'block';
                button.textContent = '📖 Lire la suite';
            }
        }

        function toggleResponse(responseIndex) {
            const shortElement = document.getElementById(`response-${responseIndex}`);
            const fullElement = document.getElementById(`full-response-${responseIndex}`);
            const button = event.target;

            if (fullElement.classList.contains('hidden')) {
                fullElement.classList.remove('hidden');
                shortElement.style.display = 'none';
                button.textContent = '📖 Voir moins';
            } else {
                fullElement.classList.add('hidden');
                shortElement.style.display = 'block';
                button.textContent = '📖 Voir la réponse complète';
            }
        }

        // === SYSTÈME D'ÉVALUATION (inchangé) ===
        if (isEvaluable) {
            console.log('🔍 Question évaluable, vérification de l\'évaluation...');
            checkEvaluationStatus();
        }

        function checkEvaluationStatus() {
            if (evaluationInProgress) return;

            const attemptCount = parseInt(sessionStorage.getItem(`eval_attempts_${questionId}`)) || 0;

            fetch(`/questions/${questionId}/evaluation/status`)
                .then(response => response.json())
                .then(data => {
                    console.log('📊 Statut évaluation:', data);

                    if (data.success) {
                        if (data.has_evaluation && data.evaluation) {
                            showEvaluationComplete(data.evaluation);
                        } else if (data.can_evaluate && attemptCount < maxAttempts) {
                            console.log('🚀 Démarrage de l\'évaluation automatique...');
                            triggerAutomaticEvaluation();
                        } else if (attemptCount >= maxAttempts) {
                            showEvaluationError('Limite de tentatives atteinte');
                        } else {
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
            const attemptCount = parseInt(sessionStorage.getItem(`eval_attempts_${questionId}`)) || 0;

            if (attemptCount >= maxAttempts) {
                showEvaluationError('Trop de tentatives');
                return;
            }

            sessionStorage.setItem(`eval_attempts_${questionId}`, (attemptCount + 1).toString());
            evaluationInProgress = true;

            fetch(`/questions/${questionId}/evaluate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Réponse non-JSON reçue');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('✅ Évaluation déclenchée:', data);

                    if (data.success) {
                        setTimeout(() => {
                            checkFinalStatus();
                        }, 5000);
                    } else {
                        console.error('❌ Erreur évaluation:', data.message);
                        showEvaluationError(data.message);
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur déclenchement évaluation:', error);
                    showEvaluationError();
                })
                .finally(() => {
                    evaluationInProgress = false;
                });
        }

        function checkFinalStatus() {
            fetch(`/questions/${questionId}/evaluation/status`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.has_evaluation && data.evaluation) {
                        showEvaluationComplete(data.evaluation);
                        sessionStorage.removeItem(`eval_attempts_${questionId}`);
                    } else {
                        showEvaluationError('Évaluation en cours');
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur vérification finale:', error);
                    showEvaluationError();
                });
        }

        function showEvaluationComplete(evaluation) {
            console.log('✅ Évaluation complète, affichage des résultats');

            const statusSpinner = document.getElementById('status-spinner');
            const statusText = document.getElementById('status-text');
            const progressBar = document.getElementById('progress-bar');

            if (statusSpinner) statusSpinner.innerHTML = '✅';
            if (statusText) statusText.textContent = '✨ Évaluation terminée ! Cliquez pour voir les détails.';
            if (progressBar) progressBar.style.width = '100%';

            const evaluationSection = document.getElementById('evaluation-section');
            if (evaluationSection) {
                const button = document.createElement('a');
                button.href = `/questions/${questionId}/evaluation`;
                button.className = 'bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-50 transition-colors inline-block mt-4';
                button.innerHTML = '📊 Voir l\'évaluation détaillée';
                evaluationSection.appendChild(button);
            }
        }

        function showEvaluationPending() {
            const statusText = document.getElementById('status-text');
            if (statusText) statusText.textContent = '⏳ Évaluation en attente...';
        }

        function showEvaluationError(message = 'Erreur d\'évaluation') {
            const statusSpinner = document.getElementById('status-spinner');
            const statusText = document.getElementById('status-text');

            if (statusSpinner) statusSpinner.innerHTML = '❌';
            if (statusText) statusText.textContent = '❌ ' + message;
        }

        // === INITIALISATION ===
        document.addEventListener('DOMContentLoaded', function() {
            console.log('🎨 Interface moderne chargée avec système d\'onglets');

            // Animation d'entrée pour les onglets
            const tabs = document.querySelectorAll('.ai-tab');
            tabs.forEach((tab, index) => {
                tab.style.animationDelay = `${index * 0.1}s`;
                tab.classList.add('animate-fadeInUp');
            });

            // Raccourcis clavier pour naviguer entre les onglets
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey || e.metaKey) {
                    switch(e.key) {
                        case '1':
                            e.preventDefault();
                            switchTab(0);
                            break;
                        case '2':
                            e.preventDefault();
                            switchTab(1);
                            break;
                        case '3':
                            e.preventDefault();
                            switchTab(2);
                            break;
                    }
                }
            });
        });

        console.log('🔧 Système moderne avec onglets superposés chargé!');
        console.log('💡 Raccourcis: Ctrl+1/2/3 pour naviguer entre les IA');
    </script>
</x-app-layout>
