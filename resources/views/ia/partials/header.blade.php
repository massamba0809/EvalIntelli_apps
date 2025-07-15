{{-- resources/views/ia/partials/header.blade.php --}}
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
