{{-- resources/views/ia/form.blade.php - VERSION ULTRA PROPRE --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Poser une question - ') }} {{ $domain->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Info domaine -->
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <div class="text-blue-500 text-2xl mr-3">💡</div>
                    <div>
                        <h3 class="font-medium text-blue-800 dark:text-blue-200 text-lg">{{ $domain->name }}</h3>
                        <p class="text-sm text-blue-600 dark:text-blue-300">
                            Vos questions seront analysées par GPT-4 Omni, DeepSeek R1 et Qwen 2.5 72B
                        </p>
                    </div>
                </div>
            </div>

            <!-- Formulaire principal -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('ia.submit', $domain) }}" id="question-form">
                        @csrf

                        <div class="mb-6">
                            <x-input-label for="question" :value="__('Votre question')" class="text-lg font-medium" />
                            <textarea
                                id="question"
                                name="question"
                                rows="6"
                                class="mt-2 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-lg shadow-sm text-base"
                                required
                                placeholder="Décrivez votre question en détail. Plus votre question est précise, meilleures seront les réponses des IA..."
                            >{{ old('question') }}</textarea>
                            <x-input-error :messages="$errors->get('question')" class="mt-2" />

                            <!-- Compteur de caractères -->
                            <div class="flex justify-between items-center mt-3">
                                <div class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">💡 Conseil :</span> Soyez précis pour obtenir de meilleures réponses
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    <span id="char-counter">0/2000 caractères</span>
                                </div>
                            </div>
                        </div>

                        <!-- Estimation du temps -->
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <div class="text-yellow-500 text-xl mr-3">⏱️</div>
                                <div class="text-sm">
                                    <strong class="text-yellow-800 dark:text-yellow-200">Temps estimé :</strong>
                                    <span class="text-yellow-700 dark:text-yellow-300">30-90 secondes selon la complexité de votre question</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <a href="{{ route('ia.index') }}"
                               class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition-colors shadow">
                                ← Retour aux domaines
                            </a>

                            <x-primary-button id="submit-button" class="py-3 px-8 text-lg">
                                {{ __('🚀 Envoyer aux 3 IA') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Exemples de questions -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mt-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                    <span class="mr-2">💡</span>
                    Exemples de questions pour ce domaine
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="examples-grid">
                    <!-- Sera rempli par JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <!-- Animation de chargement -->
    <div id="loading-screen" class="hidden fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-8 max-w-md mx-auto">
            <div class="text-center">
                <h3 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-4">
                    🤖 Traitement en cours...
                </h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Nos IA analysent votre question. Veuillez patienter...
                </p>

                <!-- Barre de progression -->
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-6">
                    <div class="loading-bar h-3 rounded-full bg-gradient-to-r from-blue-500 to-purple-600"></div>
                </div>

                <!-- IA Avatars -->
                <div class="flex justify-center space-x-6 mb-6">
                    <div class="ai-icon text-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white text-sm font-bold mb-2 shadow-lg">G4</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">GPT-4</div>
                    </div>
                    <div class="ai-icon text-center">
                        <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold mb-2 shadow-lg">DS</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">DeepSeek</div>
                    </div>
                    <div class="ai-icon text-center">
                        <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center text-white text-sm font-bold mb-2 shadow-lg">QW</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Qwen</div>
                    </div>
                </div>

                <!-- Message et temps -->
                <div class="text-sm text-gray-600 dark:text-gray-400 font-medium" id="status-message">
                    Connexion aux modèles IA...
                </div>
                <div class="mt-4 text-xs text-gray-500">
                    Temps écoulé: <span id="timer">0</span>s
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Animations de chargement */
        .loading-bar {
            animation: progress 3s ease-in-out infinite;
            width: 0%;
        }

        @keyframes progress {
            0% { width: 0%; }
            50% { width: 70%; }
            100% { width: 100%; }
        }

        .ai-icon {
            animation: float 2s ease-in-out infinite;
        }

        .ai-icon:nth-child(2) { animation-delay: 0.3s; }
        .ai-icon:nth-child(3) { animation-delay: 0.6s; }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        /* État de chargement */
        body.is-loading { overflow: hidden; }
    </style>

    <script>
        // Configuration
        const DOMAIN_ID = {{ $domain->id }};
        const DOMAIN_NAME = "{{ $domain->name }}";

        // Variables globales
        let statusMessages = [
            "Connexion aux modèles IA...",
            "Analyse de votre question...",
            "GPT-4 traite votre demande...",
            "DeepSeek génère sa réponse...",
            "Qwen analyse le contexte...",
            "Finalisation en cours..."
        ];

        let messageIndex = 0;
        let seconds = 0;
        let messageTimer = null;
        let secondsTimer = null;

        // Exemples par domaine
        const examples = {
            'programmation': [
                'Comment optimiser une requête SQL avec plusieurs jointures ?',
                'Créer une fonction Python pour calculer la suite de Fibonacci',
                'Différences entre React et Vue.js pour un projet web',
                'Meilleures pratiques pour sécuriser une API REST'
            ],
            'design': [
                'Principes de design UX/UI modernes',
                'Palette de couleurs pour une application mobile',
                'Typography pour un site web professionnel',
                'Accessibilité dans le design web'
            ],
            'default': [
                'Expliquez-moi ce concept en détail...',
                'Quelles sont les meilleures pratiques pour...',
                'Comment puis-je améliorer...',
                'Donnez-moi des conseils pour...'
            ]
        };

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            initializeForm();
            setupExamples();

            // Focus sur le textarea
            document.getElementById('question').focus();
        });

        // Configuration du formulaire
        function initializeForm() {
            const questionField = document.getElementById('question');
            const charCounter = document.getElementById('char-counter');
            const form = document.getElementById('question-form');
            const submitButton = document.getElementById('submit-button');

            // Compteur de caractères
            questionField.addEventListener('input', function() {
                const count = this.value.length;
                charCounter.textContent = `${count}/2000 caractères`;

                if (count > 1800) {
                    charCounter.className = 'text-sm text-red-500 dark:text-red-400';
                } else if (count > 1500) {
                    charCounter.className = 'text-sm text-yellow-500 dark:text-yellow-400';
                } else {
                    charCounter.className = 'text-sm text-gray-500 dark:text-gray-400';
                }
            });

            // Soumission du formulaire
            form.addEventListener('submit', function(e) {
                const questionText = questionField.value.trim();

                if (!questionText) {
                    e.preventDefault();
                    alert('Veuillez saisir une question');
                    return;
                }

                if (questionText.length > 2000) {
                    e.preventDefault();
                    alert('Votre question est trop longue (maximum 2000 caractères)');
                    return;
                }

                // Afficher l'animation de chargement
                showLoadingScreen();

                submitButton.innerHTML = '⏳ Envoi en cours...';
                submitButton.disabled = true;
            });
        }

        // Configuration des exemples
        function setupExamples() {
            const container = document.getElementById('examples-grid');
            const domainKey = DOMAIN_NAME.toLowerCase();
            const domainExamples = examples[domainKey] || examples['default'];

            container.innerHTML = domainExamples.map(example => `
                <button type="button"
                        onclick="useExample('${example.replace(/'/g, "\\'")}')"
                        class="text-left w-full p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors text-sm border border-gray-200 dark:border-gray-600">
                    <span class="text-blue-600 dark:text-blue-400 mr-2">💡</span>
                    "${example}"
                </button>
            `).join('');
        }

        // Utiliser un exemple
        function useExample(text) {
            const questionField = document.getElementById('question');
            questionField.value = text;
            questionField.focus();

            // Mettre à jour le compteur
            questionField.dispatchEvent(new Event('input'));
        }

        // Afficher l'écran de chargement
        function showLoadingScreen() {
            document.getElementById('loading-screen').classList.remove('hidden');
            document.body.classList.add('is-loading');
            startTimers();
        }

        // Démarrer les timers
        function startTimers() {
            // Timer pour les messages
            messageTimer = setInterval(function() {
                messageIndex = (messageIndex + 1) % statusMessages.length;
                document.getElementById('status-message').textContent = statusMessages[messageIndex];
            }, 3000);

            // Timer pour les secondes
            secondsTimer = setInterval(function() {
                seconds++;
                document.getElementById('timer').textContent = seconds;
            }, 1000);
        }

        // Arrêter les timers
        function stopTimers() {
            if (messageTimer) {
                clearInterval(messageTimer);
                messageTimer = null;
            }
            if (secondsTimer) {
                clearInterval(secondsTimer);
                secondsTimer = null;
            }
        }

        // Nettoyage
        window.addEventListener('beforeunload', stopTimers);

        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                document.getElementById('loading-screen').classList.add('hidden');
                document.body.classList.remove('is-loading');
                stopTimers();

                const submitButton = document.getElementById('submit-button');
                submitButton.innerHTML = '🚀 Envoyer aux 3 IA';
                submitButton.disabled = false;

                seconds = 0;
                messageIndex = 0;
            }
        });
    </script>
</x-app-layout>
