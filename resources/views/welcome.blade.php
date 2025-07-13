<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EvalIntelli | Comparez les IA en 3 clics</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.8s ease-out forwards',
                        'float': 'float 6s ease-in-out infinite',
                        'slide-up': 'slideUp 0.6s ease-out forwards',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'wave': 'wave 2.5s linear infinite',
                        'gradient-x': 'gradientX 8s ease infinite',
                        'bounce-gentle': 'bounceGentle 2s infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0) rotate(-1deg)' },
                            '50%': { transform: 'translateY(-12px) rotate(1deg)' },
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        wave: {
                            '0%': { transform: 'rotate(0deg)' },
                            '10%': { transform: 'rotate(-5deg)' },
                            '20%': { transform: 'rotate(10deg)' },
                            '30%': { transform: 'rotate(-5deg)' },
                            '40%': { transform: 'rotate(10deg)' },
                            '50%': { transform: 'rotate(0deg)' },
                            '100%': { transform: 'rotate(0deg)' },
                        },
                        gradientX: {
                            '0%, 100%': { 'background-position': '0% 50%' },
                            '50%': { 'background-position': '100% 50%' },
                        },
                        bounceGentle: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' },
                        }
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .transition-slow { transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
        .transition-fast { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }

        .btn-hover:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -10px rgba(79, 70, 229, 0.5);
        }

        .gradient-bg {
            background-size: 200% 200%;
            background-image: linear-gradient(45deg, #6366f1, #8b5cf6, #6366f1);
        }

        .card-hover {
            transition: all 0.3s ease, transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .card-hover:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .nav-link {
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: currentColor;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .simple-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-500">

<!-- Navigation Originale -->
<nav class="bg-white dark:bg-gray-800 shadow-sm py-4 px-6 sticky top-0 z-50 transition-slow">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <i class="fas fa-brain text-2xl text-indigo-600 dark:text-indigo-400 animate-wave"></i>
            <span class="text-xl font-bold text-gray-800 dark:text-white">EvalIntelli</span>
        </div>
        <div class="flex items-center space-x-4" id="auth-section">
            <!-- Le contenu sera rempli dynamiquement par JavaScript -->
            <a href="/login" class="nav-link px-4 py-1.5 dark:text-gray-200 text-gray-800 text-sm font-medium transition-fast animate-fade-in">
                Connexion
            </a>
            <a href="/register" class="nav-link px-4 py-1.5 dark:text-gray-200 text-gray-800 text-sm font-medium transition-fast animate-fade-in">
                Inscription
            </a>
            <button onclick="toggleDarkMode()" class="p-2 rounded-full text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-fast">
                <i class="fas fa-moon dark:hidden animate-pulse-slow"></i>
                <i class="fas fa-sun hidden dark:block animate-spin animate-pulse-slow" style="animation-duration: 5s"></i>
            </button>
        </div>
    </div>
</nav>

<!-- Hero Section Simplifié -->
<section class="py-16 px-6">
    <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-6 animate-fade-in">
            Comparez <span class="gradient-text">3 IA</span> en un clic
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 animate-slide-up">
            Posez votre question, obtenez 3 réponses, découvrez la meilleure.
        </p>
        <div class="flex flex-col sm:flex-row justify-center gap-4 animate-slide-up" style="animation-delay: 0.2s">
            <a href="/ia" class="px-8 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 simple-hover transition-all duration-300">
                🚀 Commencer maintenant
            </a>
            <a href="#demo" class="px-8 py-4 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-semibold hover:bg-gray-50 dark:hover:bg-gray-800 simple-hover transition-all duration-300">
                📊 Voir un exemple
            </a>
        </div>
    </div>
</section>

<!-- Démonstration Simple -->
<section id="demo" class="py-16 px-6 bg-gray-50 dark:bg-gray-800">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-center text-gray-900 dark:text-white mb-12">
            Comment ça marche ?
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
            <div class="text-center animate-fade-in">
                <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">1</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Posez votre question</h3>
                <p class="text-gray-600 dark:text-gray-300">Code, maths, traduction... tout domaine</p>
            </div>

            <div class="text-center animate-fade-in" style="animation-delay: 0.1s">
                <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">2</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">3 IA répondent</h3>
                <p class="text-gray-600 dark:text-gray-300">GPT-4, DeepSeek, Qwen simultanément</p>
            </div>

            <div class="text-center animate-fade-in" style="animation-delay: 0.2s">
                <div class="w-16 h-16 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">3</span>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Évaluation automatique</h3>
                <p class="text-gray-600 dark:text-gray-300">Scores et recommandations clairs</p>
            </div>
        </div>

        <!-- Exemple visuel simple -->
        <div class="bg-white dark:bg-gray-900 rounded-xl p-6 shadow-sm animate-fade-in" style="animation-delay: 0.3s">
            <div class="mb-4">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Question exemple :</div>
                <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                    "Comment optimiser une fonction Python ?"
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="border dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">GPT-4</span>
                        <span class="text-xs bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 px-2 py-1 rounded-full">9.2/10</span>
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-300">Optimisation détaillée avec exemples...</div>
                </div>

                <div class="border dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">DeepSeek</span>
                        <span class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 px-2 py-1 rounded-full">8.7/10</span>
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-300">Techniques avancées de profilage...</div>
                </div>

                <div class="border dark:border-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">Qwen</span>
                        <span class="text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-300 px-2 py-1 rounded-full">8.1/10</span>
                    </div>
                    <div class="text-xs text-gray-600 dark:text-gray-300">Approche modulaire recommandée...</div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <div class="text-sm font-medium text-green-800 dark:text-green-300">🏆 Recommandation : GPT-4</div>
                <div class="text-xs text-green-700 dark:text-green-400">Meilleure qualité d'explication et exemples pratiques</div>
            </div>
        </div>
    </div>
</section>

<!-- Avantages Simplifiés -->
<section class="py-16 px-6">
    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold text-center text-gray-900 dark:text-white mb-12">
            Pourquoi EvalIntelli ?
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="text-center animate-fade-in">
                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Rapide et Simple</h3>
                <p class="text-gray-600 dark:text-gray-300">Résultats en 30 secondes, interface intuitive</p>
            </div>

            <div class="text-center animate-fade-in" style="animation-delay: 0.1s">
                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Évaluation Objective</h3>
                <p class="text-gray-600 dark:text-gray-300">Scores basés sur des critères techniques précis</p>
            </div>

            <div class="text-center animate-fade-in" style="animation-delay: 0.2s">
                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Gratuit et Sécurisé</h3>
                <p class="text-gray-600 dark:text-gray-300">Aucun coût caché, vos données protégées</p>
            </div>

            <div class="text-center animate-fade-in" style="animation-delay: 0.3s">
                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tous Domaines</h3>
                <p class="text-gray-600 dark:text-gray-300">Code, maths, traduction, rédaction...</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Final -->
<section class="py-16 px-6 bg-indigo-600">
    <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl font-bold text-white mb-6">Prêt à tester ?</h2>
        <p class="text-xl text-indigo-100 mb-8">Votre première comparaison est gratuite</p>
        <div class="flex flex-col sm:flex-row justify-center gap-4">
            <a href="/ia" class="px-8 py-4 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-gray-50 simple-hover transition-all duration-300 animate-bounce-gentle">
                🎯 Essayer maintenant
            </a>
            <a href="/register" class="px-8 py-4 border-2 border-white text-white rounded-lg font-semibold hover:bg-white hover:text-indigo-600 simple-hover transition-all duration-300">
                📧 Créer un compte
            </a>
        </div>
    </div>
</section>

<!-- Footer Minimaliste -->
<footer class="py-8 px-6 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center space-x-2 mb-4 md:mb-0">
                <i class="fas fa-brain text-xl text-indigo-600 dark:text-indigo-400"></i>
                <span class="font-semibold text-gray-800 dark:text-white">EvalIntelli</span>
            </div>

            <div class="flex items-center space-x-6 text-sm">
                <a href="/ia" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Comparaison</a>
                <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">FAQ</a>
                <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Contact</a>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 text-center text-sm">
            © EvalIntelli. Interface simplifiée pour une expérience optimale.
        </div>
    </div>
</footer>

<script>
    // Dark Mode Toggle
    function toggleDarkMode() {
        document.documentElement.classList.toggle('dark');
        localStorage.setItem('darkMode', document.documentElement.classList.contains('dark'));
    }

    // Applique le mode sombre si déjà activé
    if (localStorage.getItem('darkMode') === 'true') {
        document.documentElement.classList.add('dark');
    }

    // Gestion dynamique des liens d'authentification
    function updateAuthLinks() {
        const authSection = document.getElementById('auth-section');

        // Simulation de vérification d'authentification
        const isLoggedIn = localStorage.getItem('authToken') ||
            document.cookie.includes('auth_token') ||
            false;

        if (isLoggedIn) {
            authSection.innerHTML = `
                <a href="/dashboard" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    Dashboard
                </a>
                <a href="/ia" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Nouvelle comparaison
                </a>
                <button onclick="logout()" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                    Déconnexion
                </button>
                <button onclick="toggleDarkMode()" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                    <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            `;
        } else {
            authSection.innerHTML = `
                <a href="/login" class="px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    Connexion
                </a>
                <a href="/register" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Inscription
                </a>
                <button onclick="toggleDarkMode()" class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 dark:hidden" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                    </svg>
                    <svg class="w-5 h-5 hidden dark:block" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            `;
        }
    }

    // Fonction de déconnexion
    function logout() {
        localStorage.removeItem('authToken');
        document.cookie = 'auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
        updateAuthLinks();
        window.location.href = '/login';
    }

    // Mise à jour au chargement
    document.addEventListener('DOMContentLoaded', updateAuthLinks);

    // Animation au scroll simple
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observer tous les éléments avec animation
    document.querySelectorAll('.animate-fade-in, .animate-slide-up').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
</script>
</body>
</html>
