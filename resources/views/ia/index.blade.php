{{-- resources/views/ia/index.blade.php - Version améliorée avec icônes et animations --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <!-- Bouton Hamburger amélioré -->
            <button
                id="hamburger-btn"
                type="button"
                class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-all duration-200 hover:scale-105 group"
                aria-label="Basculer le menu"
                title="Menu principal"
            >
                <span class="sr-only">Ouvrir le menu</span>
                <!-- Icon hamburger avec animation -->
                <svg id="menu-icon" class="h-6 w-6 transition-all duration-300 group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <!-- Icon close avec animation -->
                <svg id="close-icon" class="h-6 w-6 hidden transition-all duration-300 group-hover:rotate-90" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Titre avec animation -->
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight transition-all duration-300 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                {{ __('Comparaison d\'IA') }}
            </h2>

            <!-- Bouton historique pour mobile amélioré -->
            <button
                id="history-toggle-mobile"
                class="lg:hidden bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-lg transition-all duration-300 flex items-center shadow-md hover:shadow-lg"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>Historique</span>
            </button>
        </div>
    </x-slot>

    <!-- Overlay amélioré -->
    <div id="menu-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300 backdrop-blur-sm" onclick="closeMenu()"></div>

    <!-- Menu Panel amélioré -->
    <div id="menu-panel" class="fixed top-0 left-0 w-80 h-full bg-white dark:bg-gray-800 shadow-2xl transform -translate-x-full transition-all duration-300 ease-out z-50 border-r border-gray-200 dark:border-gray-700">

        <!-- Header du menu amélioré -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-gray-800 dark:to-gray-700">
            <div class="flex items-center">
                <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center mr-3 shadow-lg animate-pulse">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Historique</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Vos questions précédentes</p>
                </div>
            </div>
            <button
                onclick="closeMenu()"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 p-2 rounded-lg transition-all duration-200 hover:bg-gray-100 dark:hover:bg-gray-700 hover:scale-105"
                aria-label="Fermer le menu"
                title="Fermer"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Contenu du menu scrollable -->
        <div class="flex flex-col h-full overflow-hidden">
            <div class="flex-1 overflow-y-auto custom-scrollbar">
                <div class="p-4 space-y-6">

                    <!-- Statistiques rapides améliorées -->
                    <div id="quick-stats" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 shadow-inner border border-gray-200 dark:border-gray-600">
                        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Statistiques
                        </h4>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="text-center bg-white dark:bg-gray-800 p-2 rounded-lg shadow-sm">
                                <div class="text-lg font-bold text-blue-600 dark:text-blue-400 flex items-center justify-center" id="total-questions">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    -
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">Total</div>
                            </div>
                            <div class="text-center bg-white dark:bg-gray-800 p-2 rounded-lg shadow-sm">
                                <div class="text-lg font-bold text-green-600 dark:text-green-400 flex items-center justify-center" id="evaluated-questions">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    -
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">Évaluées</div>
                            </div>
                            <div class="text-center bg-white dark:bg-gray-800 p-2 rounded-lg shadow-sm">
                                <div class="text-lg font-bold text-purple-600 dark:text-purple-400 flex items-center justify-center" id="week-questions">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    -
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">Cette semaine</div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Historique des questions améliorée -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Historique des Questions
                            </h4>
                            <div class="flex items-center space-x-2">
                                <!-- Menu actions amélioré -->
                                <div class="relative" id="actions-menu">
                                    <button
                                        id="actions-toggle"
                                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 p-1 rounded transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
                                        title="Actions"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    <div id="actions-dropdown" class="hidden absolute right-0 top-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-lg shadow-lg z-10 min-w-48 overflow-hidden">
                                        <button
                                            onclick="toggleSelectionMode()"
                                            class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center"
                                            id="selection-mode-btn"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            Sélection multiple
                                        </button>
                                        <button
                                            onclick="confirmClearAllHistory()"
                                            class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Vider l'historique
                                        </button>
                                    </div>
                                </div>
                                <button
                                    id="refresh-history"
                                    class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 flex items-center text-sm"
                                    onclick="loadQuestionsHistory()"
                                >
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Actualiser
                                </button>
                            </div>
                        </div>

                        <!-- Mode sélection multiple amélioré -->
                        <div id="selection-controls" class="hidden mb-3 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-700">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-blue-700 dark:text-blue-300 flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span id="selected-count">0</span> sélectionnée(s)
                                </span>
                                <div class="flex items-center space-x-2">
                                    <button
                                        onclick="selectAllQuestions()"
                                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-xs underline"
                                    >
                                        Tout sélectionner
                                    </button>
                                    <button
                                        onclick="deleteSelectedQuestions()"
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs flex items-center transition-all duration-200 hover:scale-105"
                                        id="delete-selected-btn"
                                        disabled
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Supprimer
                                    </button>
                                    <button
                                        onclick="toggleSelectionMode()"
                                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 p-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Recherche dans l'historique améliorée -->
                        <div class="relative mb-3">
                            <input
                                type="text"
                                id="history-search"
                                placeholder="Rechercher... (Ctrl+K)"
                                class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg pl-10 pr-4 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Filtres améliorés -->
                        <div class="grid grid-cols-2 gap-2 mb-3">
                            <div class="relative">
                                <select id="domain-filter" class="w-full text-xs border border-gray-300 dark:border-gray-600 rounded-md pl-8 pr-2 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none">
                                    <option value="">Tous domaines</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                            </div>
                            <div class="relative">
                                <select id="status-filter" class="w-full text-xs border border-gray-300 dark:border-gray-600 rounded-md pl-8 pr-2 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white appearance-none">
                                    <option value="">Tous statuts</option>
                                    <option value="evaluated">Évaluées</option>
                                    <option value="pending">En attente</option>
                                    <option value="other">Autres</option>
                                </select>
                                <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Liste des questions améliorée -->
                        <div id="questions-history" class="space-y-3 max-h-96 overflow-y-auto">
                            <!-- Loading initial amélioré -->
                            <div class="text-center text-gray-500 dark:text-gray-400 py-8" id="history-loading">
                                <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500 mx-auto mb-3"></div>
                                <p class="text-sm font-medium">Chargement de l'historique...</p>
                                <p class="text-xs mt-1">Veuillez patienter</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer du menu amélioré -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600">
                <div class="text-center">
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Historique des questions
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenu principal -->
    <div id="main-content" class="transition-all duration-300 ease-in-out">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Message de bienvenue amélioré -->
                <div class="bg-gradient-to-r from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20 border border-indigo-200 dark:border-indigo-700 rounded-xl p-6 mb-8 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <div class="flex items-start">
                        <div class="bg-indigo-100 dark:bg-indigo-900/50 p-3 rounded-lg mr-4 animate-bounce">
                            <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-indigo-800 dark:text-indigo-200 mb-2">Bienvenue dans la comparaison d'IA</h3>
                            <p class="text-indigo-600 dark:text-indigo-300">
                                Posez vos questions à 3 IA de pointe simultanément : <strong>GPT-4 Omni</strong>, <strong>DeepSeek R1</strong> et <strong>Qwen 2.5 72B</strong>
                            </p>
                            <div class="mt-4 flex items-center text-indigo-700 dark:text-indigo-400">
                                <svg class="w-4 h-4 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm">Sélectionnez un domaine pour commencer</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-xl hover:shadow-2xl transition-shadow duration-300">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                {{ __('Choisissez un domaine') }}
                            </h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                {{ count($domains) }} domaines disponibles
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            @foreach($domains as $index => $domain)
                                <a href="{{ route('ia.form', $domain) }}"
                                   class="domain-card block p-6 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gradient-to-br hover:from-gray-50 hover:to-indigo-50 dark:hover:from-gray-700 dark:hover:to-gray-600 transition-all duration-300 group hover:shadow-xl hover:scale-105 transform hover:border-indigo-300 dark:hover:border-indigo-500"
                                   style="animation-delay: {{ $index * 0.1 }}s">
                                    <div class="flex items-center mb-4">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 shadow-lg group-hover:shadow-xl group-hover:scale-110 transition-all duration-300 group-hover:rotate-3">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                            </svg>
                                        </div>
                                        <h4 class="font-bold text-lg group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $domain->name }}</h4>
                                    </div>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm leading-relaxed group-hover:text-gray-700 dark:group-hover:text-gray-300">
                                        {{ __('Posez une question en ') }} {{ $domain->name }} et obtenez 3 perspectives d'IA différentes
                                    </p>
                                    <div class="mt-4 flex items-center text-indigo-600 dark:text-indigo-400 group-hover:text-indigo-700 dark:group-hover:text-indigo-300">
                                        <span class="text-sm font-medium">Commencer</span>
                                        <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Container amélioré -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-3 w-80"></div>

    <!-- CSS Styles améliorés avec animations -->
    <style>
        /* === ANIMATIONS D'ENTRÉE AMÉLIORÉES === */
        .domain-card {
            opacity: 0;
            transform: translateY(20px);
            animation: slideInUp 0.6s ease-out forwards;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        @keyframes slideInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Animation de rebond pour les éléments importants */
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        .animate-bounce {
            animation: bounce 1.5s infinite;
        }

        /* Pulse pour les éléments d'attention */
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        .animate-pulse {
            animation: pulse 2s infinite;
        }

        /* Rotation pour les icônes de chargement */
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        .animate-spin {
            animation: spin 1s linear infinite;
        }

        /* === STYLES POUR LES QUESTIONS AVEC COULEURS PAR DOMAINE AMÉLIORÉES === */
        .question-item {
            opacity: 0;
            transform: translateX(-20px);
            animation: slideInFromLeft 0.4s ease-out forwards;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            will-change: transform, box-shadow;
            backface-visibility: hidden;
            transform: translateZ(0);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        @keyframes slideInFromLeft {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Effet de survol avec animation du gradient amélioré */
        .question-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.7s ease-in-out;
            z-index: 1;
        }

        .question-item:hover::before {
            left: 100%;
        }

        .question-item:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .dark .question-item:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        /* Styles spécifiques selon les couleurs de domaine améliorés */
        .question-item.programming {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.08), rgba(5, 150, 105, 0.08));
        }

        .question-item.mathematics {
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.08), rgba(109, 40, 217, 0.08));
        }

        .question-item.translation {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(37, 99, 235, 0.08));
        }

        .question-item.design {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.08), rgba(219, 39, 119, 0.08));
        }

        .question-item.science {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08), rgba(79, 70, 229, 0.08));
        }

        .question-item.business {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.08), rgba(217, 119, 6, 0.08));
        }

        /* États de sélection améliorés */
        .question-item.selected {
            background-color: #dbeafe !important;
            border-color: #3b82f6 !important;
            transform: scale(1.03);
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.25);
            animation: pulseSelected 1.5s infinite;
        }

        @keyframes pulseSelected {
            0%, 100% {
                transform: scale(1.03);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .dark .question-item.selected {
            background-color: #1e3a8a !important;
            border-color: #3b82f6 !important;
            box-shadow: 0 6px 15px rgba(59, 130, 246, 0.35);
        }

        .question-item.selection-mode {
            cursor: pointer;
            border-style: dashed;
        }

        .question-item.selection-mode:hover {
            background-color: #e0f2fe !important;
            border-color: #0284c7 !important;
        }

        .dark .question-item.selection-mode:hover {
            background-color: #164e63 !important;
        }

        /* Animation pour les badges de domaine améliorée */
        .question-item .rounded-full {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .question-item:hover .rounded-full {
            transform: scale(1.08) rotate(3deg);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }

        /* === NOTIFICATIONS AMÉLIORÉES === */
        .notification {
            transform: translateX(100%);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            opacity: 0;
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        /* === BOUTONS HOVER AMÉLIORÉS === */
        .question-item .group-hover\:opacity-100 {
            opacity: 0;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
        }

        .question-item:hover .group-hover\:opacity-100 {
            opacity: 1;
            transform: scale(1.1);
        }

        /* === SCROLLBAR PERSONNALISÉE AMÉLIORÉE === */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
            transition: background 0.3s;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        /* === HAMBURGER ANIMATION AMÉLIORÉE === */
        #hamburger-btn:hover #menu-icon {
            transform: rotate(90deg);
        }

        #hamburger-btn:hover #close-icon {
            transform: rotate(90deg);
        }

        /* Animation d'entrée séquentielle améliorée */
        .question-item:nth-child(1) { animation-delay: 0.1s; }
        .question-item:nth-child(2) { animation-delay: 0.15s; }
        .question-item:nth-child(3) { animation-delay: 0.2s; }
        .question-item:nth-child(4) { animation-delay: 0.25s; }
        .question-item:nth-child(5) { animation-delay: 0.3s; }
        .question-item:nth-child(n+6) { animation-delay: 0.35s; }

        /* Amélioration des focus states pour l'accessibilité */
        .question-item:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        .question-item:focus-visible {
            box-shadow: 0 0 0 2px #fff, 0 0 0 4px #3b82f6;
        }

        /* Dark mode optimizations améliorées */
        .dark .question-item {
            border-color: #374151;
        }

        .dark .question-item:hover {
            border-color: #4b5563;
        }

        /* Réduction des animations pour les utilisateurs sensibles au mouvement */
        @media (prefers-reduced-motion: reduce) {
            .question-item,
            .question-item::before,
            .question-item .rounded-full,
            .domain-card {
                animation: none !important;
                transition: none !important;
            }

            .question-item:hover {
                transform: none !important;
            }
        }

        /* === ÉTATS RESPONSIFS AMÉLIORÉS === */
        @media (max-width: 1024px) {
            #menu-panel {
                height: 100vh;
            }
        }

        @media (max-width: 768px) {
            .question-item {
                padding: 12px;
            }

            .question-item:hover {
                transform: none !important;
                scale: 1 !important;
            }

            .question-item .group-hover\:opacity-100 {
                opacity: 1 !important;
            }

            .domain-card {
                animation-delay: 0s !important;
            }
        }

        /* Animation pour les cartes de domaine */
        @media (min-width: 768px) {
            .domain-card:hover {
                transform: translateY(-5px) scale(1.02);
            }
        }

        /* Effet de vague pour les boutons */
        .wave-effect {
            position: relative;
            overflow: hidden;
        }

        .wave-effect:after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.4);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%, -50%);
            transform-origin: 50% 50%;
        }

        .wave-effect:focus:not(:active)::after {
            animation: wave 0.6s ease-out;
        }

        @keyframes wave {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }

        /* Animation pour les icônes */
        .icon-hover-scale {
            transition: transform 0.3s ease;
        }

        .icon-hover-scale:hover {
            transform: scale(1.2);
        }
    </style>

    <!-- JavaScript amélioré avec animations fluides -->
    <script>
        console.log('🚀 Script d\'historique avec animations améliorées chargé');

        // === VARIABLES GLOBALES ===
        let menuOpen = false;
        let questionsData = [];
        let filteredQuestions = [];
        let searchTimeout;
        let selectionMode = false;
        let selectedQuestions = new Set();

        // === 🎨 SYSTÈME DE COULEURS PAR DOMAINE AMÉLIORÉ ===

        /**
         * 🎨 Détermine la couleur d'une question selon son type/domaine
         */
        function getDomainColorClasses(question) {
            const domainName = question.domain_name?.toLowerCase() || '';
            const evaluationType = question.evaluation_type || 'none';

            // 🎯 PRIORITÉ 1 : Couleurs selon le type d'évaluation détecté
            if (evaluationType === 'translation') {
                return {
                    border: 'border-l-4 border-blue-500',
                    background: 'bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20',
                    badge: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
                    icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" /></svg>',
                    label: 'Traduction'
                };
            }

            if (evaluationType === 'mathematics') {
                return {
                    border: 'border-l-4 border-purple-500',
                    background: 'bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20',
                    badge: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
                    icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>',
                    label: 'Mathématiques'
                };
            }

            if (evaluationType === 'programming' || question.is_programming) {
                return {
                    border: 'border-l-4 border-green-500',
                    background: 'bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20',
                    badge: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                    icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>',
                    label: 'Programmation'
                };
            }

            // 🎯 PRIORITÉ 2 : Couleurs selon le nom de domaine si pas d'évaluation
            const domainColorMap = {
                // Programmation
                'programmation': { color: 'green', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>', label: 'Programmation' },
                'programming': { color: 'green', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>', label: 'Programming' },
                'code': { color: 'green', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>', label: 'Code' },
                'développement': { color: 'green', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>', label: 'Développement' },
                'web': { color: 'green', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>', label: 'Web' },
                'software': { color: 'green', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>', label: 'Software' },

                // Mathématiques
                'mathématiques': { color: 'purple', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>', label: 'Mathématiques' },
                'mathematics': { color: 'purple', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>', label: 'Mathematics' },
                'math': { color: 'purple', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>', label: 'Math' },
                'logique': { color: 'purple', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>', label: 'Logique' },
                'logic': { color: 'purple', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>', label: 'Logic' },
                'calcul': { color: 'purple', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>', label: 'Calcul' },
                'algèbre': { color: 'purple', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>', label: 'Algèbre' },
                'géométrie': { color: 'purple', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>', label: 'Géométrie' },

                // Traduction/Langues
                'traduction': { color: 'blue', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" /></svg>', label: 'Traduction' },
                'translation': { color: 'blue', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" /></svg>', label: 'Translation' },
                'langues': { color: 'blue', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" /></svg>', label: 'Langues' },
                'languages': { color: 'blue', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" /></svg>', label: 'Languages' },
                'linguistique': { color: 'blue', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" /></svg>', label: 'Linguistique' },
            };

            // Rechercher une correspondance dans le nom du domaine
            for (const [keyword, config] of Object.entries(domainColorMap)) {
                if (domainName.includes(keyword)) {
                    return createColorClasses(config.color, config.icon, config.label);
                }
            }

            // 🎯 PRIORITÉ 3 : Couleur par défaut selon le statut
            if (question.has_evaluation) {
                return createColorClasses('emerald', '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>', 'Évaluée');
            }

            if (question.is_evaluable) {
                return createColorClasses('orange', '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>', 'Évaluable');
            }

            // Couleur par défaut
            return createColorClasses('gray', '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>', 'Question générale');
        }

        /**
         * 🎨 Génère les classes CSS pour une couleur donnée
         */
        function createColorClasses(color, icon, label) {
            const colorMap = {
                green: {
                    border: 'border-l-4 border-green-500',
                    background: 'bg-gradient-to-r from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20',
                    badge: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
                },
                purple: {
                    border: 'border-l-4 border-purple-500',
                    background: 'bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20',
                    badge: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
                },
                blue: {
                    border: 'border-l-4 border-blue-500',
                    background: 'bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20',
                    badge: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
                },
                indigo: {
                    border: 'border-l-4 border-indigo-500',
                    background: 'bg-gradient-to-r from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20',
                    badge: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300'
                },
                teal: {
                    border: 'border-l-4 border-teal-500',
                    background: 'bg-gradient-to-r from-teal-50 to-teal-100 dark:from-teal-900/20 dark:to-teal-800/20',
                    badge: 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-300'
                },
                emerald: {
                    border: 'border-l-4 border-emerald-500',
                    background: 'bg-gradient-to-r from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20',
                    badge: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300'
                },
                pink: {
                    border: 'border-l-4 border-pink-500',
                    background: 'bg-gradient-to-r from-pink-50 to-pink-100 dark:from-pink-900/20 dark:to-pink-800/20',
                    badge: 'bg-pink-100 text-pink-800 dark:bg-pink-900 dark:text-pink-300'
                },
                yellow: {
                    border: 'border-l-4 border-yellow-500',
                    background: 'bg-gradient-to-r from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20',
                    badge: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
                },
                orange: {
                    border: 'border-l-4 border-orange-500',
                    background: 'bg-gradient-to-r from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20',
                    badge: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'
                },
                amber: {
                    border: 'border-l-4 border-amber-500',
                    background: 'bg-gradient-to-r from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20',
                    badge: 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300'
                },
                cyan: {
                    border: 'border-l-4 border-cyan-500',
                    background: 'bg-gradient-to-r from-cyan-50 to-cyan-100 dark:from-cyan-900/20 dark:to-cyan-800/20',
                    badge: 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-300'
                },
                violet: {
                    border: 'border-l-4 border-violet-500',
                    background: 'bg-gradient-to-r from-violet-50 to-violet-100 dark:from-violet-900/20 dark:to-violet-800/20',
                    badge: 'bg-violet-100 text-violet-800 dark:bg-violet-900 dark:text-violet-300'
                },
                slate: {
                    border: 'border-l-4 border-slate-500',
                    background: 'bg-gradient-to-r from-slate-50 to-slate-100 dark:from-slate-900/20 dark:to-slate-800/20',
                    badge: 'bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-300'
                },
                gray: {
                    border: 'border-l-4 border-gray-500',
                    background: 'bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-900/20 dark:to-gray-800/20',
                    badge: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'
                }
            };

            const classes = colorMap[color] || colorMap.gray;

            return {
                ...classes,
                icon: icon,
                label: label
            };
        }

        /**
         * 🎨 Applique les couleurs spécifiques aux questions
         */
        function applyDomainColors() {
            const questionItems = document.querySelectorAll('.question-item');

            questionItems.forEach((item, index) => {
                const questionId = item.getAttribute('data-question-id');
                const question = filteredQuestions.find(q => q.id == questionId);

                if (question) {
                    const colorConfig = getDomainColorClasses(question);

                    // Appliquer les classes de couleur
                    item.className = item.className.replace(/border-l-4 border-\w+-500/g, '');
                    item.className = item.className.replace(/bg-gradient-to-r from-\w+-50 to-\w+-100 dark:from-\w+-900\/20 dark:to-\w+-800\/20/g, '');

                    // Ajouter les nouvelles classes
                    item.classList.add(...colorConfig.border.split(' '));

                    // Mise à jour du badge
                    const badge = item.querySelector('.rounded-full');
                    if (badge) {
                        badge.className = `text-xs px-2 py-1 rounded-full ${colorConfig.badge} flex items-center`;
                        badge.innerHTML = `<span class="mr-1">${colorConfig.icon}</span>${question.domain_name}`;
                    }

                    // Animation d'entrée séquentielle
                    item.style.animationDelay = `${index * 0.05}s`;
                }
            });
        }

        /**
         * 🎭 Ajoute des effets interactifs améliorés
         */
        function addInteractiveEffects() {
            const questionItems = document.querySelectorAll('.question-item');

            questionItems.forEach(item => {
                // Effet de hover amélioré
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.02)';
                    this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.15)';
                    this.style.transition = 'all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1)';

                    // Animation du badge
                    const badge = this.querySelector('.rounded-full');
                    if (badge) {
                        badge.style.transform = 'scale(1.08) rotate(3deg)';
                    }
                });

                item.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                    this.style.boxShadow = '';
                    this.style.transition = 'all 0.3s ease';

                    // Restaurer le badge
                    const badge = this.querySelector('.rounded-full');
                    if (badge) {
                        badge.style.transform = '';
                    }
                });

                // Effet de focus pour l'accessibilité amélioré
                item.addEventListener('focus', function() {
                    this.style.outline = '2px solid #3b82f6';
                    this.style.outlineOffset = '2px';
                    this.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.5)';
                });

                item.addEventListener('blur', function() {
                    this.style.outline = '';
                    this.style.outlineOffset = '';
                    this.style.boxShadow = '';
                });

                // Effet de clic
                item.addEventListener('mousedown', function() {
                    this.style.transform = 'scale(0.98)';
                });

                item.addEventListener('mouseup', function() {
                    this.style.transform = 'translateY(-3px) scale(1.02)';
                });
            });
        }

        // === GESTION DU MENU PRINCIPAL AMÉLIORÉE ===
        function toggleMenu() {
            if (menuOpen) {
                closeMenu();
            } else {
                openMenu();
            }
        }

        function openMenu() {
            console.log('📂 Ouverture du menu avec historique');
            menuOpen = true;

            const overlay = document.getElementById('menu-overlay');
            const panel = document.getElementById('menu-panel');
            const mainContent = document.getElementById('main-content');
            const menuIcon = document.getElementById('menu-icon');
            const closeIcon = document.getElementById('close-icon');

            // Gestion responsive
            if (mainContent) {
                if (window.innerWidth >= 1024) {
                    mainContent.style.marginLeft = '320px';
                    mainContent.style.transition = 'margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
                } else {
                    if (overlay) {
                        overlay.classList.remove('hidden');
                        overlay.offsetHeight; // Force reflow
                        overlay.style.opacity = '1';
                    }
                    document.body.style.overflow = 'hidden';
                }
            }

            // Ouvrir le panel avec animation fluide
            if (panel) {
                panel.style.transform = 'translateX(0)';
                panel.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.25)';
            }

            // Changer les icônes avec animation
            if (menuIcon && closeIcon) {
                menuIcon.style.transform = 'rotate(90deg) scale(0)';
                setTimeout(() => {
                    menuIcon.classList.add('hidden');
                    closeIcon.classList.remove('hidden');
                    closeIcon.style.transform = 'rotate(0deg) scale(1)';
                }, 150);
            }

            // Charger l'historique si pas encore fait
            if (questionsData.length === 0) {
                loadQuestionsHistory();
                loadUserStats();
            }
        }

        function closeMenu() {
            console.log('📁 Fermeture du menu');
            menuOpen = false;

            const overlay = document.getElementById('menu-overlay');
            const panel = document.getElementById('menu-panel');
            const mainContent = document.getElementById('main-content');
            const menuIcon = document.getElementById('menu-icon');
            const closeIcon = document.getElementById('close-icon');

            // Restaurer le contenu principal
            if (mainContent) {
                mainContent.style.marginLeft = '0';
            }

            // Masquer l'overlay avec animation
            if (overlay) {
                overlay.style.opacity = '0';
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }

            // Fermer le panel avec animation
            if (panel) {
                panel.style.transform = 'translateX(-100%)';
                panel.style.boxShadow = 'none';
            }

            // Restaurer les icônes avec animation
            if (menuIcon && closeIcon) {
                closeIcon.style.transform = 'rotate(90deg) scale(0)';
                setTimeout(() => {
                    closeIcon.classList.add('hidden');
                    menuIcon.classList.remove('hidden');
                    menuIcon.style.transform = 'rotate(0deg) scale(1)';
                }, 150);
            }

            // Réactiver le scroll
            document.body.style.overflow = '';
        }

        // === CHARGEMENT DE L'HISTORIQUE AMÉLIORÉ ===
        function loadQuestionsHistory() {
            console.log('📋 Chargement de l\'historique des questions');
            const historyContainer = document.getElementById('questions-history');
            const loadingElement = document.getElementById('history-loading');

            // Afficher le loading amélioré
            if (loadingElement) {
                loadingElement.style.display = 'block';
                loadingElement.innerHTML = `
                    <div class="flex flex-col items-center justify-center py-8">
                        <div class="relative w-12 h-12 mb-4">
                            <div class="absolute inset-0 rounded-full border-2 border-t-indigo-500 border-r-indigo-500 border-b-transparent border-l-transparent animate-spin"></div>
                            <div class="absolute inset-1 rounded-full border-2 border-t-indigo-400 border-r-indigo-400 border-b-transparent border-l-transparent animate-spin" style="animation-delay: 0.2s"></div>
                        </div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Chargement de l'historique...</p>
                        <p class="text-xs mt-1 text-gray-400 dark:text-gray-500">Veuillez patienter</p>
                    </div>
                `;
            }

            fetch('/api/user/questions')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('✅ Historique chargé:', data);
                    if (data.success) {
                        questionsData = data.questions || [];
                        populateDomainFilter(data.domains || []);
                        filterQuestionsHistory();

                        // Masquer le loading avec animation
                        if (loadingElement) {
                            loadingElement.style.opacity = '0';
                            setTimeout(() => {
                                loadingElement.style.display = 'none';
                                loadingElement.style.opacity = '1';
                            }, 300);
                        }
                    } else {
                        throw new Error(data.error || 'Erreur inconnue');
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur lors du chargement de l\'historique:', error);
                    showHistoryError('Erreur lors du chargement de l\'historique');
                });
        }

        function loadUserStats() {
            console.log('📊 Chargement des statistiques utilisateur');
            fetch('/api/user/stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateStatsDisplay(data.stats);
                    }
                })
                .catch(error => {
                    console.error('❌ Erreur lors du chargement des statistiques:', error);
                });
        }

        function updateStatsDisplay(stats) {
            document.getElementById('total-questions').innerHTML = `
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                ${stats.total_questions || 0}
            `;
            document.getElementById('evaluated-questions').innerHTML = `
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                ${stats.evaluated_questions || 0}
            `;
            document.getElementById('week-questions').innerHTML = `
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                ${stats.this_week_questions || 0}
            `;
        }

        // === FILTRAGE DES QUESTIONS AMÉLIORÉ ===
        function filterQuestionsHistory() {
            const domainFilter = document.getElementById('domain-filter').value;
            const statusFilter = document.getElementById('status-filter').value;
            const searchQuery = document.getElementById('history-search').value.toLowerCase();

            filteredQuestions = questionsData.filter(question => {
                const domainMatch = !domainFilter || question.domain_id == domainFilter;

                let statusMatch = true;
                if (statusFilter) {
                    switch(statusFilter) {
                        case 'evaluated':
                            statusMatch = question.has_evaluation;
                            break;
                        case 'pending':
                            statusMatch = question.is_programming && !question.has_evaluation && question.responses_count >= 3;
                            break;
                        case 'programming':
                            statusMatch = question.is_programming;
                            break;
                        case 'other':
                            statusMatch = !question.is_programming;
                            break;
                    }
                }

                const searchMatch = !searchQuery ||
                    question.content.toLowerCase().includes(searchQuery) ||
                    question.domain_name.toLowerCase().includes(searchQuery);

                return domainMatch && statusMatch && searchMatch;
            });

            // Tri par date (plus récent en premier)
            filteredQuestions.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));

            renderQuestionsHistory();
        }

        function renderQuestionsHistory() {
            const historyContainer = document.getElementById('questions-history');

            if (filteredQuestions.length === 0) {
                historyContainer.innerHTML = `
                    <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                        <div class="bg-gray-100 dark:bg-gray-700 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium">Aucune question trouvée</p>
                        <p class="text-xs mt-1">Posez votre première question !</p>
                    </div>
                `;
                return;
            }

            // Générer le HTML avec les couleurs
            const questionsHtml = filteredQuestions
                .slice(0, 20)
                .map((question, index) => createQuestionHistoryItem(question, index))
                .join('');

            historyContainer.innerHTML = questionsHtml;

            // Appliquer les couleurs après le rendu
            setTimeout(() => {
                applyDomainColors();
                addInteractiveEffects();
            }, 50);
        }

        function createQuestionHistoryItem(question, index) {
            const date = new Date(question.created_at).toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });

            const content = question.content.length > 80
                ? question.content.substring(0, 80) + '...'
                : question.content;

            const isProgramming = question.is_programming;
            const hasEvaluation = question.has_evaluation;
            const responsesCount = question.responses_count || 0;

            // 🎨 Obtenir les couleurs selon le domaine avec icônes SVG
            const colorConfig = getDomainColorClasses(question);

            // Déterminer le statut avec icônes SVG
            let statusIcon = '', statusText = '', statusColor = '';

            if (question.evaluation_type === 'translation') {
                if (hasEvaluation) {
                    statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>';
                    statusText = 'Évaluée';
                    statusColor = 'text-blue-600 dark:text-blue-400';
                } else if (responsesCount >= 3) {
                    statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                    statusText = 'Évaluation possible';
                    statusColor = 'text-yellow-600 dark:text-yellow-400';
                } else {
                    statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>';
                    statusText = 'En cours';
                    statusColor = 'text-blue-600 dark:text-blue-400';
                }
            } else if (question.evaluation_type === 'mathematics') {
                if (hasEvaluation) {
                    statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>';
                    statusText = 'Évaluée';
                    statusColor = 'text-purple-600 dark:text-purple-400';
                } else if (responsesCount >= 3) {
                    statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                    statusText = 'Évaluation possible';
                    statusColor = 'text-yellow-600 dark:text-yellow-400';
                } else {
                    statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>';
                    statusText = 'En cours';
                    statusColor = 'text-purple-600 dark:text-purple-400';
                }
            } else if (isProgramming) {
                if (hasEvaluation) {
                    statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>';
                    statusText = 'Évaluée';
                    statusColor = 'text-green-600 dark:text-green-400';
                } else if (responsesCount >= 3) {
                    statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                    statusText = 'Évaluation possible';
                    statusColor = 'text-yellow-600 dark:text-yellow-400';
                } else {
                    statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>';
                    statusText = 'En cours';
                    statusColor = 'text-blue-600 dark:text-blue-400';
                }
            } else {
                statusIcon = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>';
                statusText = 'Question générale';
                statusColor = 'text-gray-600 dark:text-gray-400';
            }

            const isSelected = selectedQuestions.has(question.id);

            return `
                <div class="question-item ${isSelected ? 'selected' : ''} p-4 rounded-lg border border-gray-200 dark:border-gray-600 hover:shadow-md transition-all duration-200 relative group ${colorConfig.border} ${colorConfig.background}"
                     data-question-id="${question.id}"
                     style="animation-delay: ${index * 0.05}s"
                     tabindex="0"
                     role="button">

                    <!-- Checkbox pour sélection multiple amélioré -->
                    <div class="selection-checkbox ${selectionMode ? 'block' : 'hidden'} absolute top-3 left-3 z-10">
                        <input type="checkbox"
                               ${isSelected ? 'checked' : ''}
                               onchange="toggleQuestionSelection(${question.id})"
                               onclick="event.stopPropagation()"
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 transition-all duration-200 hover:scale-110">
                    </div>

                    <!-- Contenu principal -->
                    <div class="question-content ${selectionMode ? 'ml-8' : ''}"
                         onclick="${selectionMode ? `toggleQuestionSelection(${question.id})` : `navigateToQuestion(${question.id}, ${isProgramming}, ${hasEvaluation})`}">

                        <!-- En-tête avec badge coloré amélioré -->
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs px-2 py-1 rounded-full ${colorConfig.badge} flex items-center transition-all duration-200">
                                <span class="mr-1">${colorConfig.icon}</span>
                                ${question.domain_name}
                            </span>
                            <div class="flex items-center space-x-2">
                                <span class="text-xs ${statusColor} flex items-center font-medium">
                                    <span class="mr-1">${statusIcon}</span>
                                    ${statusText}
                                </span>
                                ${!selectionMode ? `
                                    <button onclick="event.stopPropagation(); deleteQuestion(${question.id})"
                                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 opacity-0 group-hover:opacity-100 transition-opacity p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20"
                                            title="Supprimer cette question">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                ` : ''}
                            </div>
                        </div>

                        <!-- Contenu amélioré -->
                        <p class="text-sm text-gray-800 dark:text-gray-200 line-clamp-2 mb-2 leading-relaxed">
                            ${content}
                        </p>

                        <!-- Footer amélioré -->
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center text-gray-500 dark:text-gray-400">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="mr-3">${date}</span>
                                ${responsesCount > 0 ? `
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                    <span>${responsesCount}</span>
                                ` : ''}
                            </div>
                            <div class="flex items-center">
                                ${hasEvaluation ?
                '<span class="text-green-600 dark:text-green-400 font-medium flex items-center">Voir évaluation <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></span>' :
                isProgramming && responsesCount >= 3 ?
                    '<span class="text-yellow-600 dark:text-yellow-400 flex items-center">Évaluer <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></span>' :
                    '<span class="text-gray-500 dark:text-gray-400 flex items-center">Voir réponses <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg></span>'
            }
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // === NAVIGATION VERS UNE QUESTION AMÉLIORÉE ===
        function navigateToQuestion(questionId, isProgramming, hasEvaluation) {
            console.log(`🔗 Navigation vers question ${questionId}`, {
                isProgramming,
                hasEvaluation
            });

            // Animation de clic améliorée
            const questionItem = document.querySelector(`[data-question-id="${questionId}"]`);
            if (questionItem) {
                questionItem.style.transform = 'scale(0.98)';
                questionItem.style.boxShadow = '0 5px 15px rgba(0, 0, 0, 0.1)';
                setTimeout(() => {
                    questionItem.style.transform = '';
                    questionItem.style.boxShadow = '';
                }, 150);
            }

            // Détecter si c'est une question évaluable
            const questionData = questionsData.find(q => q.id === questionId);
            const isEvaluable = questionData ?
                (questionData.is_programming || questionData.is_mathematics || questionData.is_evaluable) :
                isProgramming;

            // Navigation selon le type et statut
            if (isEvaluable && hasEvaluation) {
                // Question évaluable avec évaluation → Page d'évaluation
                console.log('📊 Navigation vers page d\'évaluation');
                window.location.href = `/questions/${questionId}/evaluation`;
            } else {
                // Autres cas → Page de résultats
                console.log('📝 Navigation vers page de résultats');
                window.location.href = `/ia/results?question=${questionId}`;
            }
        }

        // === GESTION DE LA SÉLECTION MULTIPLE AMÉLIORÉE ===
        function toggleSelectionMode() {
            selectionMode = !selectionMode;
            selectedQuestions.clear();

            const selectionControls = document.getElementById('selection-controls');
            const checkboxes = document.querySelectorAll('.selection-checkbox');
            const actionDropdown = document.getElementById('actions-dropdown');
            const selectionBtn = document.getElementById('selection-mode-btn');

            if (selectionMode) {
                selectionControls.classList.remove('hidden');
                checkboxes.forEach(checkbox => checkbox.classList.remove('hidden'));
                selectionBtn.innerHTML = `
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Annuler sélection
                `;

                document.querySelectorAll('.question-item').forEach(item => {
                    item.classList.add('selection-mode');
                });
            } else {
                selectionControls.classList.add('hidden');
                checkboxes.forEach(checkbox => checkbox.classList.add('hidden'));
                selectionBtn.innerHTML = `
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Sélection multiple
                `;

                document.querySelectorAll('.question-item').forEach(item => {
                    item.classList.remove('selection-mode', 'selected');
                });

                document.querySelectorAll('.selection-checkbox input').forEach(input => {
                    input.checked = false;
                });
            }

            actionDropdown.classList.add('hidden');
            updateSelectionControls();
        }

        function toggleQuestionSelection(questionId) {
            if (selectedQuestions.has(questionId)) {
                selectedQuestions.delete(questionId);
            } else {
                selectedQuestions.add(questionId);
            }

            updateSelectionControls();
            updateQuestionVisualState(questionId);
        }

        function updateSelectionControls() {
            const selectedCount = selectedQuestions.size;
            const selectedCountSpan = document.getElementById('selected-count');
            const deleteSelectedBtn = document.getElementById('delete-selected-btn');

            if (selectedCountSpan) {
                selectedCountSpan.textContent = selectedCount;
            }

            if (deleteSelectedBtn) {
                deleteSelectedBtn.disabled = selectedCount === 0;
                deleteSelectedBtn.classList.toggle('opacity-50', selectedCount === 0);
            }
        }

        function updateQuestionVisualState(questionId) {
            const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
            const checkbox = questionElement?.querySelector('.selection-checkbox input');

            if (questionElement && checkbox) {
                const isSelected = selectedQuestions.has(questionId);
                checkbox.checked = isSelected;
                questionElement.classList.toggle('selected', isSelected);
            }
        }

        function selectAllQuestions() {
            filteredQuestions.forEach(question => {
                selectedQuestions.add(question.id);
                updateQuestionVisualState(question.id);
            });
            updateSelectionControls();
        }

        // === SUPPRESSION DE QUESTIONS AMÉLIORÉE ===
        function deleteQuestion(questionId) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cette question ? Cette action est irréversible.')) {
                return;
            }

            fetch(`/api/user/questions/${questionId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Animation de suppression améliorée
                        const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
                        if (questionElement) {
                            questionElement.style.opacity = '0';
                            questionElement.style.transform = 'translateX(-100%)';
                            questionElement.style.transition = 'all 0.3s ease';
                            setTimeout(() => {
                                questionElement.remove();
                                // Mettre à jour les données
                                questionsData = questionsData.filter(q => q.id !== questionId);
                                filteredQuestions = filteredQuestions.filter(q => q.id !== questionId);
                                loadUserStats();
                            }, 300);
                        }

                        showNotification('✅ Question supprimée avec succès', 'success');
                    } else {
                        showNotification('❌ Erreur lors de la suppression : ' + (data.error || 'Erreur inconnue'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('❌ Erreur de connexion lors de la suppression', 'error');
                });
        }

        function deleteSelectedQuestions() {
            const count = selectedQuestions.size;

            if (count === 0) return;

            if (!confirm(`Êtes-vous sûr de vouloir supprimer ${count} question(s) ? Cette action est irréversible.`)) {
                return;
            }

            const questionIds = Array.from(selectedQuestions);

            fetch('/api/user/questions', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    question_ids: questionIds
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Animation de suppression améliorée
                        questionIds.forEach(questionId => {
                            const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
                            if (questionElement) {
                                questionElement.style.opacity = '0';
                                questionElement.style.transform = 'scale(0.8)';
                                questionElement.style.transition = 'all 0.3s ease';
                                setTimeout(() => questionElement.remove(), 300);
                            }
                        });

                        // Mettre à jour les données
                        questionsData = questionsData.filter(q => !questionIds.includes(q.id));
                        filteredQuestions = filteredQuestions.filter(q => !questionIds.includes(q.id));

                        // Réinitialiser la sélection
                        selectedQuestions.clear();
                        toggleSelectionMode();

                        loadUserStats();
                        showNotification(data.message, 'success');
                    } else {
                        showNotification('❌ Erreur lors de la suppression : ' + (data.error || 'Erreur inconnue'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('❌ Erreur de connexion lors de la suppression', 'error');
                });
        }

        function confirmClearAllHistory() {
            const totalQuestions = questionsData.length;

            if (totalQuestions === 0) {
                showNotification('ℹ️ Aucune question à supprimer', 'info');
                return;
            }

            // Double confirmation améliorée
            if (!confirm(`⚠️ ATTENTION ⚠️\n\nVous êtes sur le point de supprimer TOUT votre historique (${totalQuestions} questions).\n\nCette action est IRRÉVERSIBLE et supprimera :\n- Toutes vos questions\n- Toutes les réponses IA\n- Toutes les évaluations\n\nÊtes-vous absolument certain ?`)) {
                return;
            }

            const confirmation = prompt('Pour confirmer, tapez exactement "SUPPRIMER TOUT" (sans les guillemets) :');

            if (confirmation !== 'SUPPRIMER TOUT') {
                showNotification('❌ Suppression annulée - confirmation incorrecte', 'warning');
                return;
            }

            clearAllHistory();
        }

        function clearAllHistory() {
            fetch('/api/user/history/clear', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    confirmation: 'CONFIRM_DELETE_ALL'
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const questionsList = document.getElementById('questions-history');
                        questionsList.style.opacity = '0';
                        questionsList.style.transition = 'opacity 0.3s ease';

                        setTimeout(() => {
                            questionsData = [];
                            filteredQuestions = [];
                            selectedQuestions.clear();

                            renderQuestionsHistory();
                            loadUserStats();

                            questionsList.style.opacity = '1';
                            showNotification(data.message, 'success');
                        }, 500);
                    } else {
                        showNotification('❌ Erreur : ' + (data.error || 'Erreur inconnue'), 'error');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    showNotification('❌ Erreur de connexion', 'error');
                });
        }

        // === SYSTÈME DE NOTIFICATIONS AMÉLIORÉ ===
        function showNotification(message, type = 'info') {
            // Supprimer les notifications existantes
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notif => notif.remove());

            const notification = document.createElement('div');
            notification.className = `notification fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 ease-out opacity-0 translate-y-2`;

            // Couleurs selon le type
            let icon = '';
            switch(type) {
                case 'success':
                    notification.classList.add('bg-green-500', 'text-white');
                    icon = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                    break;
                case 'error':
                    notification.classList.add('bg-red-500', 'text-white');
                    icon = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                    break;
                case 'warning':
                    notification.classList.add('bg-yellow-500', 'text-white');
                    icon = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>';
                    break;
                case 'info':
                    notification.classList.add('bg-blue-500', 'text-white');
                    icon = '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                    break;
                default:
                    notification.classList.add('bg-blue-500', 'text-white');
            }

            notification.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0 pt-0.5">
                        ${icon}
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="inline-flex text-white focus:outline-none">
                            <span class="sr-only">Fermer</span>
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            `;

            // Ajouter au DOM
            const container = document.getElementById('notification-container');
            container.appendChild(notification);

            // Animation d'entrée
            setTimeout(() => {
                notification.classList.remove('translate-y-2', 'opacity-0');
                notification.classList.add('translate-y-0', 'opacity-100');
            }, 10);

            // Suppression automatique après 5 secondes
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.classList.remove('translate-y-0', 'opacity-100');
                    notification.classList.add('translate-y-2', 'opacity-0');
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        }

        // === FONCTIONS UTILITAIRES AMÉLIORÉES ===
        function populateDomainFilter(domains) {
            const domainFilter = document.getElementById('domain-filter');
            domainFilter.innerHTML = '<option value="">Tous domaines</option>';

            domains.forEach(domain => {
                const option = document.createElement('option');
                option.value = domain.id;
                option.textContent = domain.name;
                domainFilter.appendChild(option);
            });
        }

        function showHistoryError(message) {
            const historyContainer = document.getElementById('questions-history');
            const loadingElement = document.getElementById('history-loading');

            if (loadingElement) {
                loadingElement.style.display = 'none';
            }

            historyContainer.innerHTML = `
                <div class="text-center text-red-500 py-8">
                    <div class="bg-red-100 dark:bg-red-900/20 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-8 h-8 text-red-500 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium">❌ ${message}</p>
                    <button onclick="loadQuestionsHistory()" class="mt-3 text-sm text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 underline flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Réessayer
                    </button>
                </div>
            `;
        }

        function setupHistorySearch() {
            const searchInput = document.getElementById('history-search');

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    filterQuestionsHistory();
                }, 300);
            });

            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    filterQuestionsHistory();
                }
            });
        }

        function setupActionsMenu() {
            const actionsToggle = document.getElementById('actions-toggle');
            const actionsDropdown = document.getElementById('actions-dropdown');

            if (actionsToggle && actionsDropdown) {
                actionsToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    actionsDropdown.classList.toggle('hidden');
                });

                // Fermer le dropdown en cliquant ailleurs
                document.addEventListener('click', function(e) {
                    if (!actionsDropdown.contains(e.target) && !actionsToggle.contains(e.target)) {
                        actionsDropdown.classList.add('hidden');
                    }
                });
            }
        }

        // === 📊 FONCTIONS STATISTIQUES PAR DOMAINE AMÉLIORÉES ===
        function displayDomainStatistics() {
            if (questionsData.length === 0) return;

            const stats = {};
            questionsData.forEach(question => {
                const colorConfig = getDomainColorClasses(question);
                const label = colorConfig.label;

                if (!stats[label]) {
                    stats[label] = {
                        count: 0,
                        icon: colorConfig.icon,
                        color: colorConfig.badge
                    };
                }
                stats[label].count++;
            });

            console.log('📊 Statistiques par domaine:', stats);
        }

        // === INITIALISATION COMPLÈTE AMÉLIORÉE ===
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📋 DOM chargé - Initialisation complète avec animations améliorées');

            const btn = document.getElementById('hamburger-btn');
            const overlay = document.getElementById('menu-overlay');
            const panel = document.getElementById('menu-panel');
            const mainContent = document.getElementById('main-content');

            // Configuration initiale du menu
            if (overlay) overlay.classList.add('hidden');
            if (panel) {
                panel.style.transform = 'translateX(-100%)';
                panel.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            }
            if (mainContent) {
                mainContent.style.marginLeft = '0';
                mainContent.style.transition = 'margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            }

            // Event listeners principaux améliorés
            if (btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleMenu();
                });
            }

            // Configuration de la recherche et filtres
            setupHistorySearch();
            setupActionsMenu();

            const domainFilter = document.getElementById('domain-filter');
            const statusFilter = document.getElementById('status-filter');

            if (domainFilter) {
                domainFilter.addEventListener('change', filterQuestionsHistory);
            }
            if (statusFilter) {
                statusFilter.addEventListener('change', filterQuestionsHistory);
            }

            // Animation des cartes de domaine améliorée
            setTimeout(() => {
                const domainCards = document.querySelectorAll('.domain-card');
                domainCards.forEach((card, index) => {
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 100);
                });
            }, 300);

            // Initialisation du système de couleurs
            console.log('🎨 Initialisation du système de couleurs par domaine amélioré');

            // Appliquer les couleurs après le chargement initial
            setTimeout(() => {
                if (questionsData.length > 0) {
                    renderQuestionsHistory();
                    displayDomainStatistics();
                }
            }, 500);

            console.log('🎨 Interface complète avec animations améliorées initialisée');
        });

        // === GESTION DES ÉVÉNEMENTS CLAVIER AMÉLIORÉE ===
        document.addEventListener('keydown', function(e) {
            // Escape pour fermer le menu
            if (e.key === 'Escape' && menuOpen) {
                closeMenu();
            }

            // Ctrl+K pour focus sur la recherche
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('history-search');
                if (!menuOpen) {
                    toggleMenu();
                    setTimeout(() => searchInput && searchInput.focus(), 300);
                } else {
                    searchInput && searchInput.focus();
                }
            }

            // Ctrl+M pour toggle menu
            if (e.key === 'm' && e.ctrlKey) {
                e.preventDefault();
                toggleMenu();
            }
        });

        // === GESTION RESPONSIVE AMÉLIORÉE ===
        window.addEventListener('resize', function() {
            if (menuOpen) {
                const mainContent = document.getElementById('main-content');
                if (mainContent) {
                    if (window.innerWidth >= 1024) {
                        mainContent.style.marginLeft = '320px';
                        document.body.style.overflow = '';
                        const overlay = document.getElementById('menu-overlay');
                        if (overlay) {
                            overlay.style.opacity = '0';
                            setTimeout(() => overlay.classList.add('hidden'), 300);
                        }
                    } else {
                        mainContent.style.marginLeft = '0';
                        document.body.style.overflow = 'hidden';
                        const overlay = document.getElementById('menu-overlay');
                        if (overlay) {
                            overlay.classList.remove('hidden');
                            overlay.style.opacity = '1';
                        }
                    }
                }
            }
        });

        // === FONCTIONS EXPOSÉES POUR DEBUG ===
        window.menuDebug = {
            toggle: toggleMenu,
            open: openMenu,
            close: closeMenu,
            isOpen: () => menuOpen,
            loadHistory: loadQuestionsHistory,
            questionsData: () => questionsData,
            filteredQuestions: () => filteredQuestions,
            stats: () => ({
                menuOpen,
                questionsCount: questionsData.length,
                filteredCount: filteredQuestions.length,
                selectionMode,
                selectedCount: selectedQuestions.size
            })
        };

        // 🎨 Exposer les fonctions de couleurs pour debug
        window.domainColors = {
            getDomainColorClasses,
            createColorClasses,
            applyDomainColors,
            displayDomainStatistics
        };

        console.log('💡 Debug disponible via window.menuDebug');
        console.log('🎨 Debug couleurs disponible via window.domainColors');
        console.log('💡 Raccourcis : Ctrl+M (menu), Ctrl+K (recherche), Escape (fermer)');
        console.log('📱 Support complet activé : menu, historique, sélection multiple, couleurs par domaine');
    </script>
</x-app-layout>
