{{-- resources/views/ia/partials/sidebar/header.blade.php --}}
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
