{{-- resources/views/ia/partials/sidebar/search.blade.php --}}
<!-- Barre de recherche améliorée -->
<div class="relative mb-4">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
    </div>
    <input
        type="text"
        id="search-questions"
        class="block w-full pl-10 pr-10 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all duration-200"
        placeholder="Rechercher dans l'historique..."
        autocomplete="off"
    >
    <!-- Bouton clear pour la recherche -->
    <button
        id="clear-search"
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hidden transition-colors"
        onclick="clearSearch()"
        title="Effacer la recherche"
    >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

<!-- Filtres par domaine améliorés -->
<div class="mb-4">
    <div class="flex items-center justify-between mb-2">
        <h5 class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Filtres</h5>
        <button
            onclick="clearFilters()"
            class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors"
            id="clear-filters-btn"
            style="display: none;"
        >
            Effacer
        </button>
    </div>
    <div class="flex flex-wrap gap-1" id="domain-filters">
        <!-- Les filtres seront générés dynamiquement -->
    </div>
</div>
