{{-- resources/views/ia/partials/sidebar/history.blade.php --}}
<!-- Section Historique des questions améliorée -->
<div>
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Historique des Questions
        </h4>

        @include('ia.partials.sidebar.actions')
    </div>

    @include('ia.partials.sidebar.search')

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
