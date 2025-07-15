{{-- resources/views/ia/partials/scripts.blade.php --}}
<script>
    console.log('🚀 Script d\'historique avec animations améliorées chargé');

    // === VARIABLES GLOBALES ===
    let menuOpen = false;
    let questionsData = [];
    let filteredQuestions = [];
    let searchTimeout;
    let selectionMode = false;
    let selectedQuestions = new Set();

    // === MODULE DE GESTION DES COULEURS PAR DOMAINE ===
    const DomainColors = {
        /**
         * 🎨 Détermine la couleur d'une question selon son type/domaine
         */
        getDomainColorClasses(question) {
            const domainName = question.domain_name?.toLowerCase() || '';
            const evaluationType = question.evaluation_type || 'none';

            // 🎯 PRIORITÉ 1 : Couleurs selon le type d'évaluation détecté
            if (evaluationType === 'translation') {
                return { color: 'green', badge: 'badge-translation', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" /></svg>', label: 'Traduction' };
            }

            // Mapping des domaines vers les couleurs
            const domainMapping = {
                // Programmation
                'programmation': { color: 'blue', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg>', label: 'Code' },
                'développement': { color: 'green', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>', label: 'Développement' },

                // Mathématiques
                'mathématiques': { color: 'purple', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>', label: 'Mathématiques' },

                // Traduction/Langues
                'traduction': { color: 'blue', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" /></svg>', label: 'Traduction' },
            };

            const config = domainMapping[domainName] || { color: 'gray', icon: '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>', label: 'Général' };

            // Ajouter la classe de badge
            const badgeClasses = {
                'blue': 'badge-programming',
                'purple': 'badge-mathematics',
                'green': 'badge-translation',
                'gray': 'badge-general'
            };

            return {
                ...config,
                badge: badgeClasses[config.color] || 'badge-general'
            };
        }
    };

    // === MODULE DE GESTION DU MENU ===
    const MenuManager = {
        toggle() {
            if (menuOpen) {
                this.close();
            } else {
                this.open();
            }
        },

        open() {
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
                HistoryManager.loadQuestionsHistory();
                HistoryManager.loadUserStats();
            }
        },

        close() {
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

            document.body.style.overflow = '';

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
        }
    };

    // === MODULE DE GESTION DE L'HISTORIQUE ===
    const HistoryManager = {
        async loadQuestionsHistory() {
            try {
                console.log('📋 Chargement de l\'historique des questions...');

                const response = await fetch('/api/user/questions', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();

                if (data.success) {
                    questionsData = data.questions || [];
                    filteredQuestions = [...questionsData];

                    console.log(`✅ ${questionsData.length} questions chargées`);

                    this.displayQuestions();
                    this.generateDomainFilters();
                    this.displayDomainStatistics();
                } else {
                    throw new Error(data.message || 'Erreur inconnue');
                }

            } catch (error) {
                console.error('❌ Erreur lors du chargement de l\'historique:', error);
                this.showError('Impossible de charger l\'historique des questions');
            } finally {
                const loadingElement = document.getElementById('history-loading');
                if (loadingElement) {
                    loadingElement.style.display = 'none';
                }
            }
        },

        displayQuestions() {
            const container = document.getElementById('questions-history');
            if (!container) return;

            if (filteredQuestions.length === 0) {
                container.innerHTML = `
                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2M4 13h2m-2 0L4 9m0 4l0 4" />
                    </svg>
                    <p class="text-sm font-medium">Aucune question trouvée</p>
                    <p class="text-xs mt-1">Posez votre première question pour commencer !</p>
                </div>
            `;
                return;
            }

            // Afficher les questions
            const questionsHtml = filteredQuestions.map(question => {
                const colorConfig = DomainColors.getDomainColorClasses(question);
                const createdAt = new Date(question.created_at);
                const timeAgo = this.getTimeAgo(createdAt);

                return `
                <div class="question-item bg-white dark:bg-gray-700 rounded-lg p-3 border border-gray-200 dark:border-gray-600 hover:border-indigo-300 dark:hover:border-indigo-500 cursor-pointer transition-all duration-200 group focus-ring"
                     onclick="selectQuestion(${question.id})"
                     data-question-id="${question.id}"
                     tabindex="0">

                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${colorConfig.badge}">
                                    ${colorConfig.icon}
                                    <span class="ml-1">${colorConfig.label}</span>
                                </span>
                                ${question.evaluation_status === 'completed' ?
                    '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300"><svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>Évalué</span>' : ''
                }
                            </div>

                            <h4 class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2 mb-1">
                                ${question.content.substring(0, 80)}${question.content.length > 80 ? '...' : ''}
                            </h4>

                            <div class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>${timeAgo}</span>
                            </div>
                        </div>

                        <div class="ml-2 flex-shrink-0">
                            ${selectionMode ?
                    `<input type="checkbox" class="question-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" data-question-id="${question.id}" onclick="event.stopPropagation()">` :
                    `<div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                    <button onclick="viewQuestion(${question.id}); event.stopPropagation();"
                                            class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 p-1 rounded transition-colors"
                                            title="Voir les détails">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>`
                }
                        </div>
                    </div>
                </div>
            `;
            }).join('');

            container.innerHTML = questionsHtml;
            this.addInteractiveEffects();
        },

        generateDomainFilters() {
            const container = document.getElementById('domain-filters');
            if (!container) return;

            // Compter les questions par domaine
            const domainCounts = {};
            questionsData.forEach(question => {
                const colorConfig = DomainColors.getDomainColorClasses(question);
                const label = colorConfig.label;
                domainCounts[label] = (domainCounts[label] || 0) + 1;
            });

            // Générer les filtres
            const filtersHtml = Object.entries(domainCounts).map(([domain, count]) => {
                const colorConfig = DomainColors.getDomainColorClasses({ domain_name: domain.toLowerCase() });
                return `
                <button onclick="filterByDomain('${domain}')"
                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors domain-filter"
                        data-domain="${domain}">
                    ${colorConfig.icon}
                    <span class="ml-1">${domain} (${count})</span>
                </button>
            `;
            }).join('');

            container.innerHTML = filtersHtml;
        },

        getTimeAgo(date) {
            const now = new Date();
            const diffMs = now - date;
            const diffMins = Math.floor(diffMs / 60000);
            const diffHours = Math.floor(diffMs / 3600000);
            const diffDays = Math.floor(diffMs / 86400000);

            if (diffMins < 1) return 'À l\'instant';
            if (diffMins < 60) return `${diffMins}min`;
            if (diffHours < 24) return `${diffHours}h`;
            if (diffDays < 7) return `${diffDays}j`;

            return date.toLocaleDateString('fr-FR', {
                day: 'numeric',
                month: 'short'
            });
        },

        showError(message) {
            const container = document.getElementById('questions-history');
            if (container) {
                container.innerHTML = `
                <div class="text-center text-red-500 dark:text-red-400 py-8">
                    <svg class="w-12 h-12 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <p class="text-sm font-medium">${message}</p>
                </div>
            `;
            }
        },

        addInteractiveEffects() {
            const questionItems = document.querySelectorAll('.question-item');

            questionItems.forEach(item => {
                // Effet de hover amélioré
                item.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px) scale(1.01)';
                    this.style.boxShadow = '0 10px 25px rgba(0, 0, 0, 0.1)';
                });

                item.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('selected')) {
                        this.style.transform = '';
                        this.style.boxShadow = '';
                    }
                });
            });
        },

        async loadUserStats() {
            try {
                // Simuler des statistiques
                const totalQuestions = questionsData.length;
                const thisWeekQuestions = questionsData.filter(q => {
                    const questionDate = new Date(q.created_at);
                    const oneWeekAgo = new Date();
                    oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
                    return questionDate >= oneWeekAgo;
                }).length;

                // Mettre à jour l'affichage
                const totalElement = document.getElementById('total-questions');
                const weekElement = document.getElementById('this-week-questions');

                if (totalElement) {
                    totalElement.innerHTML = totalQuestions;
                }
                if (weekElement) {
                    weekElement.innerHTML = thisWeekQuestions;
                }
            } catch (error) {
                console.error('Erreur lors du chargement des statistiques:', error);
            }
        }
    };

    // === FONCTIONS GLOBALES ===

    function selectQuestion(questionId) {
        window.location.href = `/ia/results?question_id=${questionId}`;
    }

    function viewQuestion(questionId) {
        window.location.href = `/ia/results?question_id=${questionId}`;
    }

    function toggleSelectionMode() {
        selectionMode = !selectionMode;
        const btn = document.getElementById('selection-mode-text');
        if (btn) {
            btn.textContent = selectionMode ? 'Quitter sélection' : 'Mode sélection';
        }
        HistoryManager.displayQuestions();
    }

    function filterByDomain(domain) {
        if (domain === 'all') {
            filteredQuestions = [...questionsData];
        } else {
            filteredQuestions = questionsData.filter(question => {
                const colorConfig = DomainColors.getDomainColorClasses(question);
                return colorConfig.label === domain;
            });
        }

        HistoryManager.displayQuestions();

        // Mettre à jour l'état visuel des filtres
        const filters = document.querySelectorAll('.domain-filter');
        filters.forEach(filter => {
            if (filter.dataset.domain === domain) {
                filter.classList.add('bg-indigo-100', 'text-indigo-800');
                filter.classList.remove('bg-gray-100', 'text-gray-800');
            } else {
                filter.classList.remove('bg-indigo-100', 'text-indigo-800');
                filter.classList.add('bg-gray-100', 'text-gray-800');
            }
        });
    }

    function clearFilters() {
        filteredQuestions = [...questionsData];
        HistoryManager.displayQuestions();

        // Reset des filtres visuels
        const filters = document.querySelectorAll('.domain-filter');
        filters.forEach(filter => {
            filter.classList.remove('bg-indigo-100', 'text-indigo-800');
            filter.classList.add('bg-gray-100', 'text-gray-800');
        });
    }

    function clearSearch() {
        const searchInput = document.getElementById('search-questions');
        if (searchInput) {
            searchInput.value = '';
            filteredQuestions = [...questionsData];
            HistoryManager.displayQuestions();
        }
    }

    function refreshHistory() {
        questionsData = [];
        HistoryManager.loadQuestionsHistory();
    }

    function exportQuestions() {
        // Fonction d'export (à implémenter selon vos besoins)
        console.log('Export des questions sélectionnées');
    }

    // Fonctions exposées pour compatibilité
    window.toggleMenu = MenuManager.toggle.bind(MenuManager);
    window.openMenu = MenuManager.open.bind(MenuManager);
    window.closeMenu = MenuManager.close.bind(MenuManager);

    // === INITIALISATION ===
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📋 DOM chargé - Initialisation complète');

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

        // Event listeners principaux
        if (btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                MenuManager.toggle();
            });
        }

        // Recherche en temps réel
        const searchInput = document.getElementById('search-questions');
        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const query = e.target.value.toLowerCase().trim();

                    if (query === '') {
                        filteredQuestions = [...questionsData];
                        document.getElementById('clear-search').classList.add('hidden');
                    } else {
                        filteredQuestions = questionsData.filter(question =>
                            question.content.toLowerCase().includes(query) ||
                            question.domain_name?.toLowerCase().includes(query)
                        );
                        document.getElementById('clear-search').classList.remove('hidden');
                    }

                    HistoryManager.displayQuestions();
                }, 300);
            });
        }

        // Gestion responsive
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

        // Gestion des actions dropdown
        const actionsToggle = document.getElementById('actions-toggle');
        const actionsDropdown = document.getElementById('actions-dropdown');

        if (actionsToggle && actionsDropdown) {
            actionsToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                actionsDropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', function(e) {
                if (!actionsDropdown.contains(e.target) && !actionsToggle.contains(e.target)) {
                    actionsDropdown.classList.add('hidden');
                }
            });
        }

        // Raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'm') {
                e.preventDefault();
                MenuManager.toggle();
            }
            if (e.key === 'Escape') {
                if (menuOpen) {
                    MenuManager.close();
                }
            }
        });
    });

    // Exposer les modules pour debug
    window.MenuDebug = {
        toggle: MenuManager.toggle.bind(MenuManager),
        open: MenuManager.open.bind(MenuManager),
        close: MenuManager.close.bind(MenuManager),
        isOpen: () => menuOpen,
        loadHistory: HistoryManager.loadQuestionsHistory.bind(HistoryManager),
        questionsData: () => questionsData,
        filteredQuestions: () => filteredQuestions
    };

    window.DomainColors = DomainColors;

    console.log('💡 Debug disponible via window.MenuDebug et window.DomainColors');
    console.log('💡 Raccourcis : Ctrl+M (menu), Escape (fermer)');
</script>
