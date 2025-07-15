{{-- resources/views/ia/partials/sidebar-menu.blade.php --}}
<!-- Menu Panel amélioré -->
<div id="menu-panel" class="fixed top-0 left-0 w-80 h-full bg-white dark:bg-gray-800 shadow-2xl transform -translate-x-full transition-all duration-300 ease-out z-50 border-r border-gray-200 dark:border-gray-700">

    @include('ia.partials.sidebar.header')

    <!-- Contenu du menu scrollable -->
    <div class="flex flex-col h-full overflow-hidden">
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            <div class="p-4 space-y-6">

                @include('ia.partials.sidebar.statistics')
                @include('ia.partials.sidebar.history')

            </div>
        </div>

        @include('ia.partials.sidebar.footer')
    </div>
</div>
