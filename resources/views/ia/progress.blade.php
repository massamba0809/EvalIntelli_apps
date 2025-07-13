{{-- resources/views/ia/progress.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Traitement en cours - Question #') }}{{ $question->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="container mx-auto px-4">

                <!-- Question soumise -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                        💭 Question soumise
                    </h3>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <p class="text-gray-800 dark:text-gray-200">{{ $question->content }}</p>
                    </div>
                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Domaine: <span class="font-medium">{{ $domain->name }}</span> |
                        Soumise le: {{ $question->created_at->format('d/m/Y à H:i') }}
                    </div>
                </div>

                <!-- Animation de traitement -->
                <div id="progress-container" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-8 mb-8">
                    <div class="text-center">
                        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mb-4">
                            🤖 <span id="main-status">Traitement en cours...</span>
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-8" id="status-description">
                            Nos IA analysent votre question. Cela peut prendre 30 à 90 secondes.
                        </p>

                        <!-- Barre de progression globale -->
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 mb-8">
                            <div id="global-progress" class="h-4 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 transition-all duration-1000" style="width: 0%"></div>
                        </div>

                        <!-- Statuts détaillés des IA -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <!-- GPT-4 -->
                            <div class="ai-card bg-gray-50 dark:bg-gray-700 rounded-lg p-6 transition-all duration-500" id="gpt4-card">
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center text-white text-xl font-bold mb-3 mx-auto shadow-lg">
                                        G4
                                    </div>
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">GPT-4 Omni</h4>
                                    <div id="gpt4-status" class="flex justify-center mb-2">
                                        <div class="pulse-dot w-3 h-3 bg-green-500 rounded-full mx-1"></div>
                                        <div class="pulse-dot w-3 h-3 bg-green-500 rounded-full mx-1"></div>
                                        <div class="pulse-dot w-3 h-3 bg-green-500 rounded-full mx-1"></div>
                                    </div>
                                    <div id="gpt4-text" class="text-sm text-gray-600 dark:text-gray-400">En attente...</div>
                                </div>
                            </div>

                            <!-- DeepSeek -->
                            <div class="ai-card bg-gray-50 dark:bg-gray-700 rounded-lg p-6 transition-all duration-500" id="deepseek-card">
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center text-white text-xl font-bold mb-3 mx-auto shadow-lg">
                                        DS
                                    </div>
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">DeepSeek R1</h4>
                                    <div id="deepseek-status" class="flex justify-center mb-2">
                                        <div class="pulse-dot w-3 h-3 bg-purple-500 rounded-full mx-1"></div>
                                        <div class="pulse-dot w-3 h-3 bg-purple-500 rounded-full mx-1"></div>
                                        <div class="pulse-dot w-3 h-3 bg-purple-500 rounded-full mx-1"></div>
                                    </div>
                                    <div id="deepseek-text" class="text-sm text-gray-600 dark:text-gray-400">En attente...</div>
                                </div>
                            </div>

                            <!-- Qwen -->
                            <div class="ai-card bg-gray-50 dark:bg-gray-700 rounded-lg p-6 transition-all duration-500" id="qwen-card">
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-orange-500 rounded-full flex items-center justify-center text-white text-xl font-bold mb-3 mx-auto shadow-lg">
                                        QW
                                    </div>
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 mb-2">Qwen 2.5 72B</h4>
                                    <div id="qwen-status" class="flex justify-center mb-2">
                                        <div class="pulse-dot w-3 h-3 bg-orange-500 rounded-full mx-1"></div>
                                        <div class="pulse-dot w-3 h-3 bg-orange-500 rounded-full mx-1"></div>
                                        <div class="pulse-dot w-3 h-3 bg-orange-500 rounded-full mx-1"></div>
                                    </div>
                                    <div id="qwen-text" class="text-sm text-gray-600 dark:text-gray-400">En attente...</div>
                                </div>
                            </div>
                        </div>

                        <!-- Messages de statut rotatifs -->
                        <div class="text-lg text-gray-600 dark:text-gray-400 font-medium mb-6" id="rotating-message">
                            Initialisation des connexions IA...
                        </div>
                    </div>
                </div>

                <!-- Statistiques en temps réel -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">📊 Informations</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600" id="elapsed-time">0s</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Temps écoulé</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-green-600" id="responses-completed">0/3</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Réponses complétées</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-purple-600" id="estimated-remaining">~60s</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Temps estimé restant</div>
                        </div>
                        <div class="text-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="text-2xl font-bold text-orange-600" id="refresh-count">0</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Vérifications</div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="text-center">
                    <button
                        type="button"
                        onclick="window.history.back()"
                        class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors mr-4"
                    >
                        ← Retour
                    </button>
                    <button
                        type="button"
                        onclick="forceRefresh()"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg transition-colors"
                        id="refresh-btn"
                    >
                        🔄 Actualiser
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Animations existantes */
        .pulse-dot {
            animation: pulse 1.5s ease-in-out infinite;
        }
        .pulse-dot:nth-child(2) { animation-delay: 0.5s; }
        .pulse-dot:nth-child(3) { animation-delay: 1s; }

        @keyframes pulse {
            0%, 80%, 100% { transform: scale(0); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }

        .ai-card.completed {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
        }

        .ai-card.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            transform: scale(0.95);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        }

        .ai-card.processing {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            animation: processing-glow 2s ease-in-out infinite;
        }

        @keyframes processing-glow {
            0%, 100% { box-shadow: 0 5px 15px rgba(59, 130, 246, 0.3); }
            50% { box-shadow: 0 10px 30px rgba(59, 130, 246, 0.6); }
        }
    </style>

    <script>
        const questionId = {{ $question->id }};
        let elapsedSeconds = 0;
        let completedResponses = 0;
        let refreshCount = 0;
        let statusCheckInterval;
        let timeInterval;
        let messageInterval;

        const rotatingMessages = [
            "Connexion aux serveurs IA...",
            "Analyse de votre question...",
            "Génération des réponses...",
            "Optimisation du contenu...",
            "Vérification de la qualité...",
            "Finalisation en cours..."
        ];
        let currentMessageIndex = 0;

        // Démarrer le suivi au chargement
        document.addEventListener('DOMContentLoaded', function() {
            startProgressTracking();
        });

        function startProgressTracking() {
            // Vérifier le statut toutes les 3 secondes
            statusCheckInterval = setInterval(checkStatus, 3000);

            // Mettre à jour le temps écoulé
            timeInterval = setInterval(updateTimer, 1000);

            // Changer les messages rotatifs
            messageInterval = setInterval(rotateMessage, 4000);

            // Première vérification immédiate
            checkStatus();
        }

        function checkStatus() {
            refreshCount++;
            document.getElementById('refresh-count').textContent = refreshCount;

            fetch(`/questions/${questionId}/progress/status`)
                .then(response => response.json())
                .then(data => {
                    console.log('Status update:', data);
                    updateStatus(data);
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showError('Erreur de connexion. Vérification en cours...');
                });
        }

        function updateStatus(data) {
            const status = data.status;
            const models = data.models || {};

            // Mettre à jour le statut global
            updateGlobalStatus(status);

            // Mettre à jour les statuts individuels des IA
            updateModelStatus('gpt4', 'openai/gpt-4o', models['openai/gpt-4o'] || 'pending');
            updateModelStatus('deepseek', 'deepseek/deepseek-r1', models['deepseek/deepseek-r1'] || 'pending');
            updateModelStatus('qwen', 'qwen/qwen-2.5-72b-instruct', models['qwen/qwen-2.5-72b-instruct'] || 'pending');

            // Calculer le nombre de réponses complétées
            const completed = Object.values(models).filter(status => status === 'completed').length;
            updateCompletedCount(completed);

            // Si terminé, rediriger
            if (status === 'completed') {
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 2000);
            }

            // Si erreur, afficher le message
            if (status === 'error') {
                showError(data.error_message || 'Une erreur est survenue');
            }
        }

        function updateGlobalStatus(status) {
            const mainStatus = document.getElementById('main-status');
            const description = document.getElementById('status-description');
            const progress = document.getElementById('global-progress');

            switch(status) {
                case 'pending':
                    mainStatus.textContent = 'Initialisation...';
                    description.textContent = 'Préparation du traitement de votre question.';
                    progress.style.width = '5%';
                    break;
                case 'processing':
                    mainStatus.textContent = 'Traitement en cours...';
                    description.textContent = 'Les IA analysent votre question et génèrent leurs réponses.';
                    progress.style.width = '30%';
                    break;
                case 'completed':
                    mainStatus.textContent = 'Traitement terminé!';
                    description.textContent = 'Toutes les réponses ont été générées. Redirection en cours...';
                    progress.style.width = '100%';
                    stopAllIntervals();
                    break;
                case 'error':
                    mainStatus.textContent = 'Erreur de traitement';
                    description.textContent = 'Une erreur est survenue pendant le traitement.';
                    progress.style.width = '0%';
                    progress.classList.add('bg-red-500');
                    stopAllIntervals();
                    break;
            }
        }

        function updateModelStatus(modelKey, fullModelName, status) {
            const card = document.getElementById(`${modelKey}-card`);
            const statusDiv = document.getElementById(`${modelKey}-status`);
            const textDiv = document.getElementById(`${modelKey}-text`);

            // Réinitialiser les classes
            card.classList.remove('completed', 'error', 'processing');

            switch(status) {
                case 'pending':
                    textDiv.textContent = 'En attente...';
                    break;
                case 'processing':
                    card.classList.add('processing');
                    textDiv.textContent = 'Traitement...';
                    break;
                case 'completed':
                    card.classList.add('completed');
                    statusDiv.innerHTML = '<div class="text-white font-bold text-lg">✓</div>';
                    textDiv.textContent = 'Terminé!';
                    break;
                case 'error':
                    card.classList.add('error');
                    statusDiv.innerHTML = '<div class="text-white font-bold text-lg">✗</div>';
                    textDiv.textContent = 'Erreur';
                    break;
            }
        }

        function updateCompletedCount(count) {
            completedResponses = count;
            document.getElementById('responses-completed').textContent = `${count}/3`;

            // Mettre à jour la barre de progression
            const progress = document.getElementById('global-progress');
            const percentage = 30 + (count * 20); // 30% base + 20% par réponse
            progress.style.width = `${percentage}%`;

            // Mettre à jour le temps restant estimé
            const remaining = Math.max(0, (3 - count) * 20);
            document.getElementById('estimated-remaining').textContent = remaining > 0 ? `~${remaining}s` : '0s';
        }

        function updateTimer() {
            elapsedSeconds++;
            document.getElementById('elapsed-time').textContent = `${elapsedSeconds}s`;
        }

        function rotateMessage() {
            currentMessageIndex = (currentMessageIndex + 1) % rotatingMessages.length;
            document.getElementById('rotating-message').textContent = rotatingMessages[currentMessageIndex];
        }

        function showError(message) {
            const messageDiv = document.getElementById('rotating-message');
            messageDiv.textContent = `❌ ${message}`;
            messageDiv.classList.add('text-red-500');
        }

        function stopAllIntervals() {
            clearInterval(statusCheckInterval);
            clearInterval(timeInterval);
            clearInterval(messageInterval);
        }

        function forceRefresh() {
            const btn = document.getElementById('refresh-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳ Actualisation...';
            btn.disabled = true;

            checkStatus();

            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        }

        // Nettoyer les intervalles si l'utilisateur quitte la page
        window.addEventListener('beforeunload', stopAllIntervals);
    </script>
</x-app-layout>
