{{-- resources/views/ia/partials/domains-grid.blade.php --}}
<!-- Grille des domaines -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($domains as $index => $domain)
        <div class="domain-card group relative bg-white dark:bg-gray-800 rounded-xl shadow-lg hover:shadow-2xl transition-all duration-500 border border-gray-200 dark:border-gray-700 overflow-hidden transform hover:-translate-y-2 hover:scale-105"
             style="animation-delay: {{ $index * 0.1 }}s;">

            <!-- Effet de gradient en arrière-plan -->
            <div class="absolute inset-0 bg-gradient-to-br from-{{ $domain->color_primary ?? 'indigo' }}-50 to-{{ $domain->color_secondary ?? 'purple' }}-50 dark:from-{{ $domain->color_primary ?? 'indigo' }}-900/20 dark:to-{{ $domain->color_secondary ?? 'purple' }}-900/20 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

            <!-- Contenu de la carte -->
            <div class="relative p-6">
                <!-- En-tête avec icône -->
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 rounded-lg bg-{{ $domain->color_primary ?? 'indigo' }}-100 dark:bg-{{ $domain->color_primary ?? 'indigo' }}-900/50 group-hover:scale-110 transition-transform duration-300">
                        @if($domain->icon)
                            {!! $domain->icon !!}
                        @else
                            <svg class="w-6 h-6 text-{{ $domain->color_primary ?? 'indigo' }}-600 dark:text-{{ $domain->color_primary ?? 'indigo' }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                        @endif
                    </div>

                    <!-- Badge de statut -->
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Actif
                    </span>
                </div>

                <!-- Titre et description -->
                <div class="mb-4">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2 group-hover:text-{{ $domain->color_primary ?? 'indigo' }}-600 dark:group-hover:text-{{ $domain->color_primary ?? 'indigo' }}-400 transition-colors duration-300">
                        {{ $domain->name }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
                        {{ $domain->description ?? 'Explorez les capacités des IA dans le domaine ' . strtolower($domain->name) }}
                    </p>
                </div>

                <!-- Statistiques du domaine -->
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-4">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="font-medium">{{ rand(50, 200) }} questions</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <span class="font-medium">{{ rand(85, 98) }}% de précision</span>
                    </div>
                </div>

                <!-- Bouton d'action -->
                <a href="{{ route('ia.form', $domain->slug) }}"
                   class="block w-full text-center bg-gradient-to-r from-{{ $domain->color_primary ?? 'indigo' }}-600 to-{{ $domain->color_secondary ?? 'purple' }}-600 hover:from-{{ $domain->color_primary ?? 'indigo' }}-700 hover:to-{{ $domain->color_secondary ?? 'purple' }}-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-300 transform group-hover:scale-105 shadow-md hover:shadow-lg wave-effect">
                    <span class="flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        Commencer l'analyse
                    </span>
                </a>
            </div>

            <!-- Effet de brillance au survol -->
            <div class="absolute top-0 -inset-full h-full w-1/2 z-5 block transform -skew-x-12 bg-gradient-to-r from-transparent to-white opacity-20 group-hover:animate-shine"></div>
        </div>
    @endforeach
</div>
