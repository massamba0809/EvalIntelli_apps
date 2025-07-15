{{-- resources/views/ia/partials/sidebar/statistics.blade.php --}}
<!-- Statistiques rapides améliorées -->
<div id="quick-stats" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 shadow-inner border border-gray-200 dark:border-gray-600">
    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider flex items-center mb-3">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        Statistiques Rapides
    </h4>
    <div class="grid grid-cols-2 gap-3">
        <div class="text-center">
            <div class="flex items-center justify-center">
                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400" id="total-questions">
                    <svg class="w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Total</div>
            </div>
        </div>
        <div class="text-center">
            <div class="flex items-center justify-center">
                <div class="text-2xl font-bold text-green-600 dark:text-green-400" id="this-week-questions">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    -
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Cette semaine</div>
            </div>
        </div>
    </div>
</div>
