{{-- resources/views/ia/evaluation.blade.php - VERSION ULTRA AMÉLIORÉE --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight font-sans">
            <i class="fas fa-clipboard-check mr-2 animate-spin-slow"></i>{{ __('Évaluation - Question #') }}{{ $question->id }}
        </h2>
    </x-slot>

    <div class="py-8 animate-fadeIn">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="container mx-auto px-4">

                <!-- En-tête avec animation de gradient -->
                <div class="bg-gradient-to-r {{
                    $evaluation_type === 'mathematics' ? 'from-purple-600 to-indigo-600' :
                    ($evaluation_type === 'translation' ? 'from-green-600 to-blue-600' :
                    ($evaluation_type === 'chemistry' ? 'from-orange-600 to-red-600' : 'from-blue-600 to-purple-600'))
                }} text-white rounded-xl p-6 mb-8 shadow-lg transform transition-all duration-500 hover:scale-[1.01] animate-gradient-flow">
                    <div class="flex items-center">
                        <div class="text-4xl mr-4 animate-float">
                            {{
                                $evaluation_type === 'mathematics' ? '🧮' :
                                ($evaluation_type === 'translation' ? '🌐' :
                                ($evaluation_type === 'chemistry' ? '🧪' : '💻'))
                            }}
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold mb-2 font-mono tracking-wide">
                                {{
                                    $evaluation_type === 'mathematics' ? 'Évaluation Mathématique' :
                                    ($evaluation_type === 'translation' ? 'Évaluation de Traduction' :
                                    ($evaluation_type === 'chemistry' ? 'Évaluation de Chimie' : 'Évaluation de Programmation'))
                                }}
                            </h1>
                            <p class="text-blue-100 flex items-center">
                                <i class="fas fa-robot mr-2 animate-pulse"></i>
                                {{
                                    $evaluation_type === 'mathematics' ? 'Analyse automatique avec référence Wolfram Alpha' :
                                    ($evaluation_type === 'translation' ? 'Analyse automatique avec référence DeepL' :
                                    ($evaluation_type === 'chemistry' ? 'Analyse automatique des réponses chimiques' : 'Analyse automatique de la qualité du code'))
                                }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Question originale avec apparition séquentielle -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-8 transform transition-all duration-500 hover:-translate-y-1 hover:shadow-xl animate-sequence-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                        <i class="fas fa-question-circle mr-2 text-indigo-500 animate-bounce"></i>Question analysée
                    </h3>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border-l-4 border-indigo-500 animate-border-pulse">
                        <p class="text-gray-800 dark:text-gray-200 font-serif">{{ $question->content }}</p>
                    </div>
                    <div class="mt-3 text-sm text-gray-500 dark:text-gray-400 flex flex-wrap gap-4">
                        <span class="flex items-center animate-fadeInLeft"><i class="fas fa-tag mr-1"></i> Domaine: <span class="font-medium ml-1">{{ $question->domain->name }}</span></span>
                        <span class="flex items-center animate-fadeInLeft delay-100"><i class="fas fa-code mr-1"></i> Type: <span class="font-medium ml-1">{{
                            $evaluation_type === 'mathematics' ? 'Mathématiques' :
                            ($evaluation_type === 'translation' ? 'Traduction' :
                            ($evaluation_type === 'chemistry' ? 'Chimie' : 'Programmation'))
                        }}</span></span>
                        <span class="flex items-center animate-fadeInLeft delay-200"><i class="far fa-clock mr-1"></i> Posée le: <span class="font-medium ml-1">{{ $question->created_at->format('d/m/Y à H:i') }}</span></span>
                    </div>
                </div>

                @if($evaluation)
                    <!-- Référence Wolfram Alpha avec animation de fond -->
                    @if($evaluation_type === 'mathematics')
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-8 transform transition-all duration-500 hover:-translate-y-1 animate-sequence-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <i class="fas fa-square-root-alt mr-2 text-purple-500 animate-spin-slow"></i>Référence Wolfram Alpha
                                @if($evaluation->hasWolframReference())
                                    <span class="ml-2 px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs rounded-full flex items-center animate-pulse">
                                        <i class="fas fa-check-circle mr-1"></i>Disponible
                                    </span>
                                @else
                                    <span class="ml-2 px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 text-xs rounded-full flex items-center animate-pulse">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Non disponible
                                    </span>
                                @endif
                            </h3>

                            @if($evaluation->hasWolframReference())
                                <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-4 mb-4 animate-fadeIn">
                                    <div class="font-mono text-sm whitespace-pre-wrap text-gray-800 dark:text-gray-200">{{ $evaluation->wolfram_reference }}</div>
                                </div>
                                <div class="flex flex-wrap items-center text-sm text-gray-600 dark:text-gray-400 gap-4">
                                    <span class="flex items-center animate-fadeInLeft"><i class="far fa-clock mr-1"></i> Temps de réponse: {{ number_format($evaluation->wolfram_response_time ?? 0, 2) }}s</span>
                                    <span class="flex items-center animate-fadeInLeft delay-100"><i class="fas fa-ruler-horizontal mr-1"></i> Longueur: {{ strlen($evaluation->wolfram_reference) }} caractères</span>
                                </div>
                            @else
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 flex items-start animate-shake">
                                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 mt-1 animate-pulse"></i>
                                    <p class="text-yellow-800 dark:text-yellow-200">
                                        Wolfram Alpha n'a pas pu fournir de référence pour cette question.
                                        L'évaluation se base uniquement sur la justesse mathématique et la cohérence des réponses.
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Référence DeepL avec animation de vague -->
                    @if($evaluation_type === 'translation')
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-8 transform transition-all duration-500 hover:-translate-y-1 animate-sequence-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <i class="fas fa-language mr-2 text-green-500 animate-wave"></i>Référence DeepL
                                @if(isset($evaluation->deepl_reference) && !empty($evaluation->deepl_reference))
                                    <span class="ml-2 px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs rounded-full flex items-center animate-pulse">
                                        <i class="fas fa-check-circle mr-1"></i>Disponible
                                    </span>
                                @else
                                    <span class="ml-2 px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 text-xs rounded-full flex items-center animate-pulse">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Non disponible
                                    </span>
                                @endif
                            </h3>

                            @if(isset($evaluation->deepl_reference) && !empty($evaluation->deepl_reference))
                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4 mb-4 animate-fadeIn">
                                    <div class="font-mono text-sm whitespace-pre-wrap text-gray-800 dark:text-gray-200">{{ $evaluation->deepl_reference }}</div>
                                </div>
                                <div class="flex flex-wrap items-center text-sm text-gray-600 dark:text-gray-400 gap-4">
                                    <span class="flex items-center animate-fadeInLeft"><i class="far fa-clock mr-1"></i> Temps de réponse: {{ number_format($evaluation->deepl_response_time ?? 0, 2) }}s</span>
                                    <span class="flex items-center animate-fadeInLeft delay-100"><i class="fas fa-ruler-horizontal mr-1"></i> Longueur: {{ strlen($evaluation->deepl_reference) }} caractères</span>
                                    @if(isset($evaluation->translation_data) && is_array($evaluation->translation_data))
                                        <span class="flex items-center animate-fadeInLeft delay-200"><i class="fas fa-exchange-alt mr-1"></i> {{ $evaluation->translation_data['source_language'] ?? 'AUTO' }} → {{ $evaluation->translation_data['target_language'] ?? 'FR' }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 flex items-start animate-shake">
                                    <i class="fas fa-exclamation-triangle text-yellow-500 mr-2 mt-1 animate-pulse"></i>
                                    <p class="text-yellow-800 dark:text-yellow-200">
                                        DeepL n'a pas pu fournir de référence pour cette traduction.
                                        L'évaluation se base uniquement sur la qualité linguistique intrinsèque.
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Section spéciale pour la chimie -->
                    @if($evaluation_type === 'chemistry')
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-8 transform transition-all duration-500 hover:-translate-y-1 animate-sequence-2">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <i class="fas fa-flask mr-2 text-orange-500 animate-wave"></i>Analyse Chimique
                                <span class="ml-2 px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs rounded-full flex items-center animate-pulse">
                                    <i class="fas fa-check-circle mr-1"></i>Effectuée
                                </span>
                            </h3>
                            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-4 mb-4 animate-fadeIn">
                                <p class="text-gray-800 dark:text-gray-200">Analyse automatique des réponses chimiques basée sur les données scientifiques et les équations.</p>
                            </div>
                        </div>
                    @endif

                    <!-- Résultats de l'évaluation avec animations en cascade -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-8 transform transition-all duration-500 hover:-translate-y-1 animate-sequence-3">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <i class="fas fa-trophy mr-2 text-yellow-500 animate-tada"></i>Résultats de l'évaluation
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <!-- Meilleure IA -->
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-xl p-4 text-center transform transition-all duration-500 hover:scale-105 animate-float-slow">
                                <div class="text-3xl mb-2 animate-spin-slow">👑</div>
                                <div class="text-sm text-green-600 dark:text-green-400 font-medium flex justify-center items-center">
                                    <i class="fas fa-crown mr-1"></i>Meilleure IA
                                </div>
                                <div class="text-xl font-bold text-green-800 dark:text-green-300 mt-2 animate-pulse">
                                    {{ $evaluation->best_ai_name }}
                                </div>
                            </div>

                            <!-- Scores individuels avec icônes personnalisées -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-xl p-4 text-center transform transition-all duration-500 hover:scale-105 animate-float-delay-1">
                                <div class="text-3xl mb-2">
                                    <div class="ai-icon gpt-icon animate-bounce"></div>
                                </div>
                                <div class="text-sm text-blue-600 dark:text-blue-400 font-medium flex justify-center items-center">
                                    <i class="fab fa-openai mr-1"></i>GPT-4 Omni
                                </div>
                                <div class="text-xl font-bold text-blue-800 dark:text-blue-300 mt-2 animate-count-up" data-target="{{ $evaluation->note_gpt4 ?? 0 }}">
                                    0/10
                                </div>
                            </div>

                            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-xl p-4 text-center transform transition-all duration-500 hover:scale-105 animate-float-delay-2">
                                <div class="text-3xl mb-2">
                                    <div class="ai-icon deepseek-icon animate-wiggle"></div>
                                </div>
                                <div class="text-sm text-purple-600 dark:text-purple-400 font-medium flex justify-center items-center">
                                    <i class="fas fa-brain mr-1"></i>DeepSeek R1
                                </div>
                                <div class="text-xl font-bold text-purple-800 dark:text-purple-300 mt-2 animate-count-up" data-target="{{ $evaluation->note_deepseek ?? 0 }}">
                                    0/10
                                </div>
                            </div>

                            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-xl p-4 text-center transform transition-all duration-500 hover:scale-105 animate-float-delay-3">
                                <div class="text-3xl mb-2">
                                    <div class="ai-icon qwen-icon animate-spin-slow"></div>
                                </div>
                                <div class="text-sm text-orange-600 dark:text-orange-400 font-medium flex justify-center items-center">
                                    <i class="fas fa-bolt mr-1"></i>Qwen 2.5 72B
                                </div>
                                <div class="text-xl font-bold text-orange-800 dark:text-orange-300 mt-2 animate-count-up" data-target="{{ $evaluation->note_qwen ?? 0 }}">
                                    0/10
                                </div>
                            </div>
                        </div>

                        <!-- Score moyen avec animation de comptage -->
                        <div class="text-center animate-pulse">
                            <div class="text-sm text-gray-600 dark:text-gray-400 flex justify-center items-center">
                                <i class="fas fa-chart-line mr-2 animate-updown"></i>Score moyen
                            </div>
                            <div class="text-3xl font-bold text-gray-800 dark:text-gray-200 mt-2 animate-count-up" data-target="{{ number_format($evaluation->average_score, 1) }}">
                                0.0/10
                            </div>
                        </div>
                    </div>

                    <!-- Analyse globale avec apparition progressive -->
                    @if($evaluation->commentaire_global)
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-8 transform transition-all duration-500 hover:-translate-y-1 animate-sequence-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                                <i class="fas fa-comment-dots mr-2 text-blue-500 animate-blink"></i>Analyse globale
                                @if($evaluation_type === 'mathematics')
                                    par Claude
                                @elseif($evaluation_type === 'translation')
                                    par Claude
                                @elseif($evaluation_type === 'chemistry')
                                    par Claude
                                @else
                                    par Claude
                                @endif
                            </h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border-l-4 border-blue-500 animate-border-glow">
                                <p class="text-gray-800 dark:text-gray-200 leading-relaxed font-serif animate-text-reveal">{{ $evaluation->commentaire_global }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Évaluations détaillées par IA avec animations séquentielles -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        @foreach(['gpt4' => 'GPT-4 Omni', 'deepseek' => 'DeepSeek R1', 'qwen' => 'Qwen 2.5 72B'] as $aiKey => $aiName)
                            @php
                                $score = $evaluation->getScoreForModel($aiKey);
                                $details = $evaluation->getEvaluationDetails($aiKey);
                                $isBest = $evaluation->isBestAI($aiKey);
                            @endphp

                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden transform transition-all duration-500 hover:-translate-y-1 hover:shadow-xl {{ $isBest ? 'ring-2 ring-green-500 animate-pulse-glow' : 'animate-sequence-' . ($loop->index + 5) }}">
                                <!-- En-tête de l'IA avec animation de gradient -->
                                <div class="bg-gradient-to-r {{ $isBest ? 'from-green-500 to-green-600' : 'from-gray-500 to-gray-600' }} text-white p-4 animate-gradient-hover">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-lg flex items-center">
                                            @if($isBest)<i class="fas fa-crown mr-2 animate-spin-slow"></i>@endif
                                            <span class="ai-name">{{ $aiName }}</span>
                                        </h4>
                                        <div class="bg-white bg-opacity-20 rounded-full px-3 py-1 flex items-center animate-pulse-slow">
                                            <span class="font-bold text-lg text-gray-900 dark:text-blue-950">
                                                {{ $score ?? 'N/A' }}/10
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4">
                                    @if($details && is_array($details))
                                        <!-- Critères avec animations progressives -->
                                        <div class="space-y-3 mb-4">
                                            @if($evaluation_type === 'mathematics')
                                                <!-- Critères mathématiques -->
                                                @foreach([
                                                    'coherence_reference' => 'Cohérence avec Référence',
                                                    'justesse_math' => 'Justesse Mathématique',
                                                    'clarte_explication' => 'Clarté de l\'Explication',
                                                    'notation_rigueur' => 'Notation et Rigueur',
                                                    'pertinence_raisonnement' => 'Pertinence du Raisonnement',
                                                    'hallucination' => 'Absence d\'Hallucination'
                                                ] as $criterionKey => $criterionName)
                                                    @php
                                                        $criterionScore = isset($details[$criterionKey]) && is_numeric($details[$criterionKey])
                                                            ? (int)$details[$criterionKey] : 0;
                                                        $percentage = $criterionScore > 0 ? ($criterionScore / 2) * 100 : 0;
                                                        $analysisKey = $criterionKey . '_analyse';
                                                        $criterionAnalysis = $details[$analysisKey] ?? null;
                                                        if (empty($criterionAnalysis) && isset($details[$criterionKey]) && is_string($details[$criterionKey])) {
                                                            $criterionAnalysis = $details[$criterionKey];
                                                        }
                                                    @endphp
                                                    <div class="animate-fadeInRight delay-{{ $loop->index * 100 }}">
                                                        <div class="flex justify-between text-sm mb-1">
                                                            <span class="font-medium text-gray-700 dark:text-gray-300 flex items-center">
                                                                <i class="fas fa-{{
                                                                    $criterionKey === 'coherence_reference' ? 'link' :
                                                                    ($criterionKey === 'justesse_math' ? 'check-circle' :
                                                                    ($criterionKey === 'clarte_explication' ? 'lightbulb' :
                                                                    ($criterionKey === 'notation_rigueur' ? 'pen-fancy' :
                                                                    ($criterionKey === 'pertinence_raisonnement' ? 'brain' : 'ghost'))))
                                                                }} mr-1 text-xs animate-spin-once"></i>
                                                                {{ $criterionName }}
                                                            </span>
                                                            <span class="text-gray-600 dark:text-gray-400">{{ $criterionScore }}/2</span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                                            <div class="bg-purple-600 h-2 rounded-full transition-all duration-1000 ease-out animate-progress" style="width: 0" data-width="{{ $percentage }}%"></div>
                                                        </div>
                                                        @if(!empty($criterionAnalysis) && is_string($criterionAnalysis) && strlen($criterionAnalysis) > 10))
                                                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-400 animate-text-reveal">
                                                            {{ Str::limit($criterionAnalysis, 120) }}
                                                        </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @elseif($evaluation_type === 'translation')
                                                <!-- Critères de traduction -->
                                                @foreach([
                                                    'fidelite' => 'Fidélité au sens',
                                                    'qualite_linguistique' => 'Qualité linguistique',
                                                    'style' => 'Style et ton',
                                                    'precision_contextuelle' => 'Précision contextuelle',
                                                    'hallucination' => 'Absence d\'hallucination'
                                                ] as $criterionKey => $criterionName)
                                                    @php
                                                        $criterionScore = isset($details[$criterionKey]) && is_numeric($details[$criterionKey])
                                                            ? (int)$details[$criterionKey] : 0;
                                                        $percentage = $criterionScore > 0 ? ($criterionScore / 2) * 100 : 0;
                                                        $analysisKey = $criterionKey . '_analyse';
                                                        $criterionAnalysis = $details[$analysisKey] ?? null;
                                                        if (empty($criterionAnalysis) && isset($details[$criterionKey]) && is_string($details[$criterionKey])) {
                                                            $criterionAnalysis = $details[$criterionKey];
                                                        }
                                                    @endphp
                                                    <div class="animate-fadeInRight delay-{{ $loop->index * 100 }}">
                                                        <div class="flex justify-between text-sm mb-1">
                                                            <span class="font-medium text-gray-700 dark:text-gray-300 flex items-center">
                                                                <i class="fas fa-{{
                                                                    $criterionKey === 'fidelite' ? 'balance-scale' :
                                                                    ($criterionKey === 'qualite_linguistique' ? 'language' :
                                                                    ($criterionKey === 'style' ? 'pen-nib' :
                                                                    ($criterionKey === 'precision_contextuelle' ? 'crosshairs' : 'ghost')))
                                                                }} mr-1 text-xs animate-spin-once"></i>
                                                                {{ $criterionName }}
                                                            </span>
                                                            <span class="text-gray-600 dark:text-gray-400">{{ $criterionScore }}/2</span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                                            <div class="bg-green-600 h-2 rounded-full transition-all duration-1000 ease-out animate-progress" style="width: 0" data-width="{{ $percentage }}%"></div>
                                                        </div>
                                                        @if(!empty($criterionAnalysis) && is_string($criterionAnalysis) && strlen($criterionAnalysis) > 10))
                                                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-400 animate-text-reveal">
                                                            {{ Str::limit($criterionAnalysis, 120) }}
                                                        </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @elseif($evaluation_type === 'chemistry')
                                                <!-- Critères de chimie CORRIGÉS -->
                                                @foreach([
                                                    'exactitude_scientifique' => 'Exactitude Scientifique',
                                                    'completude' => 'Complétude de la Réponse',
                                                    'clarte_explications' => 'Clarté des Explications',
                                                    'terminologie_chimique' => 'Terminologie Chimique',
                                                    'coherence_logique' => 'Cohérence Logique',
                                                    'references_sources' => 'Références/Sources'
                                                ] as $criterionKey => $criterionName)
                                                    @php
                                                        // 🔧 LOGIQUE CORRIGÉE basée sur la structure JSON trouvée
                                                        $criterionScore = $details[$criterionKey] ?? 0;
                                                        $criterionAnalysis = $details[$criterionKey . '_analyse'] ?? '';

                                                        $percentage = ($criterionScore / 2) * 100;
                                                    @endphp

                                                    <div class="mb-3 animate-slide-in-left" style="animation-delay: {{ $loop->index * 0.1 }}s">
                                                        <div class="flex justify-between items-center mb-1">
                                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                                                                <i class="fas fa-flask mr-1 text-xs text-orange-500"></i>
                                                                {{ $criterionName }}
                                                            </span>
                                                            <span class="text-gray-600 dark:text-gray-400">{{ $criterionScore }}/2</span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                                            <div class="bg-orange-600 h-2 rounded-full transition-all duration-1000 ease-out animate-progress"
                                                                 style="width: 0" data-width="{{ $percentage }}%"></div>
                                                        </div>
                                                        @if(!empty($criterionAnalysis) && strlen($criterionAnalysis) > 3)
                                                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-400 animate-text-reveal">
                                                                {{ Str::limit($criterionAnalysis, 120) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @else
                                                <!-- Critères de programmation -->
                                                @foreach([
                                                    'correctitude' => 'Correctitude',
                                                    'qualite_code' => 'Qualité du Code',
                                                    'modularite' => 'Modularité',
                                                    'pertinence' => 'Pertinence',
                                                    'explication' => 'Explication'
                                                ] as $criterionKey => $criterionName)
                                                    @php
                                                        $criterionScore = isset($details[$criterionKey]) && is_numeric($details[$criterionKey])
                                                            ? (int)$details[$criterionKey] : 0;
                                                        $percentage = $criterionScore > 0 ? ($criterionScore / 2) * 100 : 0;
                                                        $analysisKey = $criterionKey . '_analyse';
                                                        $criterionAnalysis = $details[$analysisKey] ?? null;
                                                        if (empty($criterionAnalysis) && isset($details[$criterionKey]) && is_string($details[$criterionKey])) {
                                                            $criterionAnalysis = $details[$criterionKey];
                                                        }
                                                    @endphp
                                                    <div class="animate-fadeInRight delay-{{ $loop->index * 100 }}">
                                                        <div class="flex justify-between text-sm mb-1">
                                                            <span class="font-medium text-gray-700 dark:text-gray-300 flex items-center">
                                                                <i class="fas fa-{{
                                                                    $criterionKey === 'correctitude' ? 'check-double' :
                                                                    ($criterionKey === 'qualite_code' ? 'code' :
                                                                    ($criterionKey === 'modularite' ? 'cubes' :
                                                                    ($criterionKey === 'pertinence' ? 'bullseye' : 'comment-dots')))
                                                                }} mr-1 text-xs animate-spin-once"></i>
                                                                {{ $criterionName }}
                                                            </span>
                                                            <span class="text-gray-600 dark:text-gray-400">{{ $criterionScore }}/2</span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-1000 ease-out animate-progress" style="width: 0" data-width="{{ $percentage }}%"></div>
                                                        </div>
                                                        @if(!empty($criterionAnalysis) && is_string($criterionAnalysis) && strlen($criterionAnalysis) > 10))
                                                        <div class="mt-1 text-xs text-gray-600 dark:text-gray-400 animate-text-reveal">
                                                            {{ Str::limit($criterionAnalysis, 120) }}
                                                        </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>

                                        <!-- Hallucination avec animation spéciale -->
                                        @if($evaluation_type === 'mathematics' || $evaluation_type === 'translation' || $evaluation_type === 'chemistry')
                                            @php
                                                $hallucinationAnalysis = $details['hallucination_analyse'] ?? null;
                                                if (empty($hallucinationAnalysis) && isset($details['hallucination']) && is_string($details['hallucination'])) {
                                                    $hallucinationAnalysis = $details['hallucination'];
                                                }
                                            @endphp
                                            @if(!empty($hallucinationAnalysis) && is_string($hallucinationAnalysis) && strlen($hallucinationAnalysis) > 10))
                                            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg border-l-4 border-red-500 animate-border-alert">
                                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 flex items-center">
                                                    <i class="fas fa-ghost mr-1 text-red-500 animate-ghost"></i>Détection d'hallucination :
                                                </div>
                                                <p class="text-sm text-gray-700 dark:text-gray-300 animate-text-reveal">{{ $hallucinationAnalysis }}</p>
                                            </div>
                                            @endif
                                        @endif

                                        <!-- Commentaire détaillé avec apparition progressive -->
                                        @if(isset($details['commentaire']) && !empty($details['commentaire']))
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 border-l-4 border-indigo-500 animate-border-glow">
                                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 flex items-center">
                                                    <i class="fas fa-search mr-1 text-indigo-500 animate-pulse"></i>Analyse détaillée :
                                                </div>
                                                <p class="text-sm text-gray-700 dark:text-gray-300 animate-text-reveal">{{ $details['commentaire'] }}</p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center text-gray-500 dark:text-gray-400 py-4 animate-shake">
                                            <div class="text-4xl mb-2 animate-pulse">⚠️</div>
                                            <p>Détails d'évaluation non disponibles</p>
                                            <p class="text-xs mt-1">Les critères détaillés n'ont pas pu être récupérés</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Réponses originales avec animation de défilement -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-8 transform transition-all duration-500 hover:-translate-y-1 animate-sequence-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <i class="fas fa-comments mr-2 text-blue-500 animate-wave"></i>
                            {{
                                $evaluation_type === 'mathematics' ? 'Réponses mathématiques' :
                                ($evaluation_type === 'translation' ? 'Réponses de traduction' :
                                ($evaluation_type === 'chemistry' ? 'Réponses chimiques' : 'Réponses de code'))
                            }} des IA
                        </h3>

                        <div class="space-y-6">
                            @foreach($responses as $aiKey => $response)
                                @php
                                    $aiNames = ['gpt4' => 'GPT-4 Omni', 'deepseek' => 'DeepSeek R1', 'qwen' => 'Qwen 2.5 72B'];
                                    $aiName = $aiNames[$aiKey] ?? $aiKey;
                                    $score = $evaluation->getScoreForModel($aiKey);
                                @endphp

                                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden transform transition-all duration-500 hover:shadow-xl animate-fadeInUp delay-{{ $loop->index * 100 }}">
                                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-medium text-gray-900 dark:text-white flex items-center">
                                                @if($aiKey === 'gpt4')
                                                    <div class="ai-icon gpt-icon-sm mr-2"></div>
                                                @elseif($aiKey === 'deepseek')
                                                    <div class="ai-icon deepseek-icon-sm mr-2"></div>
                                                @else
                                                    <div class="ai-icon qwen-icon-sm mr-2"></div>
                                                @endif
                                                {{ $aiName }}
                                            </h4>
                                            <div class="flex items-center space-x-2">
                                                <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs px-2 py-1 rounded flex items-center animate-pulse-slow">
                                                    <i class="fas fa-star mr-1"></i>Score: {{ $score ?? 'N/A' }}/10
                                                </span>
                                                @if($response->response_time))
                                                <span class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                        <i class="far fa-clock mr-1"></i>{{ number_format($response->response_time, 2) }}s
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <div class="prose dark:prose-invert max-w-none">
                                            <div class="relative group">
                                                <pre class="whitespace-pre-wrap text-sm bg-gray-100 dark:bg-gray-800 p-3 rounded border overflow-x-auto max-h-80 overflow-y-auto animate-scroll-reveal"><code>{{ $response->cleaned_response ?? $response->response }}</code></pre>
                                                <button onclick="copyResponseContent(this)" class="absolute top-2 right-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 p-1 rounded text-xs flex items-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                                    <i class="far fa-copy mr-1"></i>Copier
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Métadonnées avec animation de tuiles -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 mb-8 transform transition-all duration-500 hover:-translate-y-1 animate-sequence-9">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                            <i class="fas fa-database mr-2 text-indigo-500 animate-spin-slow"></i>Métadonnées de l'évaluation
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-{{
                            $evaluation_type === 'mathematics' ? '4' :
                            ($evaluation_type === 'translation' ? '4' :
                            ($evaluation_type === 'chemistry' ? '4' : '3'))
                        }} gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 animate-tile">
                                <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                    <i class="fas fa-tag mr-1"></i>Type d'évaluation
                                </div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{
                                        $evaluation_type === 'mathematics' ? 'Mathématiques' :
                                        ($evaluation_type === 'translation' ? 'Traduction' :
                                        ($evaluation_type === 'chemistry' ? 'Chimie' : 'Programmation'))
                                    }}
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 animate-tile delay-100">
                                <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                    <i class="fas fa-coins mr-1"></i>Tokens utilisés
                                </div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white animate-count-up" data-target="{{ number_format($evaluation->token_usage_evaluation ?? 0) }}">
                                    0
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 animate-tile delay-200">
                                <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                    <i class="fas fa-stopwatch mr-1"></i>Temps d'évaluation
                                </div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $evaluation->response_time_evaluation ? number_format($evaluation->response_time_evaluation, 2) . 's' : 'N/A' }}
                                </div>
                            </div>

                            @if($evaluation_type === 'mathematics')
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 animate-tile delay-300">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                        <i class="fas fa-square-root-alt mr-1"></i>Wolfram Alpha
                                    </div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                        @if($evaluation->hasWolframReference())
                                            <i class="fas fa-check-circle text-green-500 mr-1 animate-pulse"></i>Utilisé
                                        @else
                                            <i class="fas fa-times-circle text-red-500 mr-1 animate-pulse"></i>Non disponible
                                        @endif
                                    </div>
                                </div>
                            @elseif($evaluation_type === 'translation')
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 animate-tile delay-300">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                        <i class="fas fa-language mr-1"></i>DeepL
                                    </div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                        @if(isset($evaluation->deepl_reference) && !empty($evaluation->deepl_reference))
                                            <i class="fas fa-check-circle text-green-500 mr-1 animate-pulse"></i>Utilisé
                                        @else
                                            <i class="fas fa-times-circle text-red-500 mr-1 animate-pulse"></i>Non disponible
                                        @endif
                                    </div>
                                </div>
                            @elseif($evaluation_type === 'chemistry')
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 animate-tile delay-300">
                                    <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                        <i class="fas fa-flask mr-1"></i>Analyse chimique
                                    </div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                                        <i class="fas fa-check-circle text-green-500 mr-1 animate-pulse"></i>Effectuée
                                    </div>
                                </div>
                            @endif

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 animate-tile delay-400">
                                <div class="text-sm text-gray-600 dark:text-gray-400 flex items-center">
                                    <i class="far fa-calendar-alt mr-1"></i>Évaluée le
                                </div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $evaluation->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>

                @else
                    <!-- Pas d'évaluation avec animation dramatique -->
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-xl p-6 text-center transform transition-all duration-500 hover:scale-[1.01] animate-heartbeat">
                        <div class="text-4xl mb-4 animate-bounce">❌</div>
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-2 animate-pulse">
                            Évaluation manquante
                        </h3>
                        <p class="text-red-700 dark:text-red-300 mb-4 animate-fadeIn">
                            Cette question n'a pas d'évaluation disponible. L'évaluation automatique a peut-être échoué.
                        </p>
                        <a href="{{ route('ia.results.by.id', ['question' => $question->id]) }}"
                           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center justify-center animate-bounce-in">
                            <i class="fas fa-arrow-left mr-2"></i>Retour aux résultats
                        </a>
                    </div>
                @endif

                <!-- Navigation avec animations -->
                <div class="flex flex-col sm:flex-row justify-between mt-8 gap-4">
                    <a href="{{ route('ia.index') }}"
                       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-all duration-500 flex items-center justify-center animate-fadeInLeft hover:-translate-x-1">
                        <i class="fas fa-arrow-left mr-2"></i>Retour à l'accueil
                    </a>

                    <div class="flex flex-col sm:flex-row gap-4">
                        @if($evaluation)
                            <form method="POST" action="{{ route('questions.reprocess', $question) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Êtes-vous sûr de vouloir relancer l\'évaluation ?')"
                                        class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg transition-all duration-500 flex items-center justify-center animate-fadeInDown hover:-translate-y-1">
                                    <i class="fas fa-sync-alt mr-2 animate-spin-slow"></i>Relancer l'évaluation
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('ia.form', $question->domain) }}"
                           class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-all duration-500 flex items-center justify-center animate-fadeInRight hover:translate-x-1">
                            <i class="fas fa-plus mr-2 animate-pulse"></i>Nouvelle question
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Typographie améliorée */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto+Mono:wght@400;500&family=Playfair+Display:wght@400;500;600&display=swap');

        .font-sans {
            font-family: 'Inter', sans-serif;
        }

        .font-serif {
            font-family: 'Playfair Display', serif;
        }

        .font-mono {
            font-family: 'Roboto Mono', monospace;
        }

        /* Icônes personnalisées pour les IA */
        .ai-icon {
            display: inline-block;
            width: 48px;
            height: 48px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .ai-icon-sm {
            width: 24px;
            height: 24px;
        }

        .gpt-icon {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%234169E1' d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z'/%3E%3C/svg%3E");
        }

        .deepseek-icon {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%237955B8' d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z'/%3E%3C/svg%3E");
        }

        .qwen-icon {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23FF9800' d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z'/%3E%3C/svg%3E");
            filter: drop-shadow(0 0 2px rgba(255, 152, 0, 0.7));
        }

        .gpt-icon-sm {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%234169E1' d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z'/%3E%3C/svg%3E");
            width: 20px;
            height: 20px;
        }

        .deepseek-icon-sm {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%237955B8' d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z'/%3E%3C/svg%3E");
            width: 20px;
            height: 20px;
        }

        .qwen-icon-sm {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath fill='%23FF9800' d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z'/%3E%3C/svg%3E");
            width: 20px;
            height: 20px;
            filter: drop-shadow(0 0 1px rgba(255, 152, 0, 0.7));
        }

        /* Animations avancées */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            }
            50% {
                box-shadow: 0 0 20px 10px rgba(16, 185, 129, 0);
            }
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes tada {
            0% {
                transform: scale(1);
            }
            10%, 20% {
                transform: scale(0.9) rotate(-3deg);
            }
            30%, 50%, 70%, 90% {
                transform: scale(1.1) rotate(3deg);
            }
            40%, 60%, 80% {
                transform: scale(1.1) rotate(-3deg);
            }
            100% {
                transform: scale(1) rotate(0);
            }
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes wave {
            0%, 100% {
                transform: rotate(0deg);
            }
            25% {
                transform: rotate(5deg);
            }
            75% {
                transform: rotate(-5deg);
            }
        }

        @keyframes updown {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-3px);
            }
        }

        @keyframes wiggle {
            0%, 100% {
                transform: rotate(0deg);
            }
            25% {
                transform: rotate(3deg);
            }
            75% {
                transform: rotate(-3deg);
            }
        }

        @keyframes blink {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-5px);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(5px);
            }
        }

        @keyframes border-pulse {
            0%, 100% {
                border-color: #818cf8;
            }
            50% {
                border-color: #a5b4fc;
            }
        }

        @keyframes border-glow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.7);
            }
            50% {
                box-shadow: 0 0 10px 5px rgba(99, 102, 241, 0);
            }
        }

        @keyframes border-alert {
            0%, 100% {
                border-color: #ef4444;
            }
            50% {
                border-color: #fca5a5;
            }
        }

        @keyframes ghost {
            0%, 100% {
                opacity: 1;
                transform: translateY(0);
            }
            50% {
                opacity: 0.5;
                transform: translateY(-5px);
            }
        }

        @keyframes text-reveal {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scroll-reveal {
            from {
                opacity: 0;
                max-height: 0;
            }
            to {
                opacity: 1;
                max-height: 20rem;
            }
        }

        @keyframes tile {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes heartbeat {
            0%, 100% {
                transform: scale(1);
            }
            25% {
                transform: scale(1.02);
            }
            50% {
                transform: scale(1);
            }
            75% {
                transform: scale(1.01);
            }
        }

        @keyframes bounce-in {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
                opacity: 1;
            }
            100% {
                transform: scale(1);
            }
        }

        @keyframes spin-once {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes gradient-flow {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

        @keyframes gradient-hover {
            0%, 100% {
                background-size: 100% 100%;
            }
            50% {
                background-size: 150% 150%;
            }
        }

        @keyframes progress {
            from {
                width: 0;
            }
            to {
                width: var(--progress-width);
            }
        }

        @keyframes count-up {
            from {
                content: "0";
            }
            to {
                content: var(--target);
            }
        }

        @keyframes slide-in-left {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Classes d'animation */
        .animate-fadeIn {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .animate-fadeInLeft {
            animation: fadeInLeft 0.6s ease-out forwards;
        }

        .animate-fadeInRight {
            animation: fadeInRight 0.6s ease-out forwards;
        }

        .animate-fadeInDown {
            animation: fadeInDown 0.6s ease-out forwards;
        }

        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .animate-pulse-glow {
            animation: pulse-glow 2s infinite;
        }

        .animate-bounce {
            animation: bounce 1s infinite;
        }

        .animate-tada {
            animation: tada 1s;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        .animate-float-slow {
            animation: float 4s ease-in-out infinite;
        }

        .animate-float-delay-1 {
            animation: float 4s ease-in-out infinite 0.5s;
        }

        .animate-float-delay-2 {
            animation: float 4s ease-in-out infinite 1s;
        }

        .animate-float-delay-3 {
            animation: float 4s ease-in-out infinite 1.5s;
        }

        .animate-spin-slow {
            animation: spin 8s linear infinite;
        }

        .animate-spin-once {
            animation: spin-once 0.5s ease-out forwards;
        }

        .animate-wave {
            animation: wave 2s ease-in-out infinite;
        }

        .animate-updown {
            animation: updown 2s ease-in-out infinite;
        }

        .animate-wiggle {
            animation: wiggle 1s ease-in-out infinite;
        }

        .animate-blink {
            animation: blink 1.5s ease-in-out infinite;
        }

        .animate-shake {
            animation: shake 0.5s;
        }

        .animate-border-pulse {
            animation: border-pulse 2s infinite;
        }

        .animate-border-glow {
            animation: border-glow 2s infinite;
        }

        .animate-border-alert {
            animation: border-alert 1s infinite;
        }

        .animate-ghost {
            animation: ghost 2s ease-in-out infinite;
        }

        .animate-text-reveal {
            animation: text-reveal 0.8s ease-out forwards;
        }

        .animate-scroll-reveal {
            animation: scroll-reveal 0.8s ease-out forwards;
        }

        .animate-tile {
            animation: tile 0.6s ease-out forwards;
        }

        .animate-heartbeat {
            animation: heartbeat 1.5s ease-in-out infinite;
        }

        .animate-bounce-in {
            animation: bounce-in 0.6s ease-out forwards;
        }

        .animate-gradient-flow {
            background-size: 200% 200%;
            animation: gradient-flow 8s ease infinite;
        }

        .animate-gradient-hover {
            background-size: 100% 100%;
            transition: background-size 0.5s ease;
        }

        .animate-gradient-hover:hover {
            background-size: 150% 150%;
        }

        .animate-progress {
            animation: progress 1.5s ease-out forwards;
        }

        .animate-count-up {
            animation: count-up 1.5s ease-out forwards;
        }

        .animate-slide-in-left {
            animation: slide-in-left 0.6s ease-out forwards;
        }

        /* Délais d'animation */
        .delay-100 {
            animation-delay: 0.1s;
        }

        .delay-200 {
            animation-delay: 0.2s;
        }

        .delay-300 {
            animation-delay: 0.3s;
        }

        .delay-400 {
            animation-delay: 0.4s;
        }

        .delay-500 {
            animation-delay: 0.5s;
        }

        /* Séquence d'apparition */
        .animate-sequence-1 {
            animation: fadeInUp 0.6s ease-out 0.1s forwards;
            opacity: 0;
        }

        .animate-sequence-2 {
            animation: fadeInUp 0.6s ease-out 0.3s forwards;
            opacity: 0;
        }

        .animate-sequence-3 {
            animation: fadeInUp 0.6s ease-out 0.5s forwards;
            opacity: 0;
        }

        .animate-sequence-4 {
            animation: fadeInUp 0.6s ease-out 0.7s forwards;
            opacity: 0;
        }

        .animate-sequence-5 {
            animation: fadeInUp 0.6s ease-out 0.9s forwards;
            opacity: 0;
        }

        .animate-sequence-6 {
            animation: fadeInUp 0.6s ease-out 1.1s forwards;
            opacity: 0;
        }

        .animate-sequence-7 {
            animation: fadeInUp 0.6s ease-out 1.3s forwards;
            opacity: 0;
        }

        .animate-sequence-8 {
            animation: fadeInUp 0.6s ease-out 1.5s forwards;
            opacity: 0;
        }

        .animate-sequence-9 {
            animation: fadeInUp 0.6s ease-out 1.7s forwards;
            opacity: 0;
        }

        /* Styles globaux améliorés */
        .prose pre {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1rem;
            overflow-x: auto;
            transition: all 0.3s ease;
        }

        .dark .prose pre {
            background-color: #374151;
            border-color: #4b5563;
            color: #f9fafb;
        }

        /* Effets hover améliorés */
        .hover\:-translate-y-1:hover {
            transform: translateY(-4px);
        }

        .hover\:scale-105:hover {
            transform: scale(1.05);
        }

        /* Ombres portées améliorées */
        .shadow-md {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .dark .shadow-md {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
        }

        .hover\:shadow-xl:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .dark .hover\:shadow-xl:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2);
        }

        /* Bordures latérales colorées */
        .border-l-4 {
            border-left-width: 4px;
        }

        /* Meilleure IA mise en valeur */
        .ring-2.ring-green-500 {
            box-shadow: 0 0 0 2px #10b981, 0 8px 25px rgba(16, 185, 129, 0.3);
            transition: all 0.3s ease;
        }

        /* Bouton copier dans le code */
        .relative:hover button {
            opacity: 1;
        }

        .relative button {
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        /* Responsive design amélioré */
        @media (max-width: 768px) {
            .grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }

            .lg\:grid-cols-3 {
                grid-template-columns: 1fr;
            }

            .md\:grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }

            .space-x-4 > * + * {
                margin-left: 0;
                margin-top: 0.5rem;
            }

            .flex-col {
                flex-direction: column;
            }
        }
    </style>

    <script>
        // Script amélioré pour la page d'évaluation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📊 Page d\'évaluation chargée avec les améliorations UX');

            // Animation d'entrée progressive pour les cartes
            const cards = document.querySelectorAll('.bg-white.dark\\:bg-gray-800');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });

            // Animation des barres de progression
            const progressBars = document.querySelectorAll('.animate-progress');
            progressBars.forEach(bar => {
                const width = bar.getAttribute('data-width');
                bar.style.setProperty('--progress-width', width);
                bar.style.width = width;
            });

            // Animation de comptage pour les scores
            const countUps = document.querySelectorAll('.animate-count-up');
            countUps.forEach(element => {
                const target = parseFloat(element.getAttribute('data-target'));
                const isDecimal = target % 1 !== 0;
                const duration = 1500;
                const start = 0;
                const increment = target / (duration / 16);

                let current = start;
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        clearInterval(timer);
                        current = target;
                    }
                    element.textContent = isDecimal ? current.toFixed(1) + '/10' : Math.floor(current) + '/10';
                }, 16);
            });

            // Bouton copier amélioré
            window.copyResponseContent = function(button) {
                const responseContent = button.closest('.relative').querySelector('pre code').textContent;
                navigator.clipboard.writeText(responseContent).then(() => {
                    const originalHTML = button.innerHTML;
                    button.innerHTML = '<i class="fas fa-check mr-1"></i>Copié';
                    button.classList.add('bg-green-500', 'text-white');
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.classList.remove('bg-green-500', 'text-white');
                    }, 2000);
                }).catch(err => {
                    console.error('Erreur de copie:', err);
                    button.innerHTML = '<i class="fas fa-times mr-1"></i>Erreur';
                    button.classList.add('bg-red-500', 'text-white');
                    setTimeout(() => {
                        button.innerHTML = originalHTML;
                        button.classList.remove('bg-red-500', 'text-white');
                    }, 2000);
                });
            };

            // Effet parallaxe léger sur le header
            window.addEventListener('scroll', function() {
                const header = document.querySelector('header');
                const scrollPosition = window.pageYOffset;
                header.style.transform = `translateY(${scrollPosition * 0.3}px)`;
            });

            // Animation au survol des tuiles de métadonnées
            const tiles = document.querySelectorAll('.animate-tile');
            tiles.forEach((tile, index) => {
                tile.style.animationDelay = `${index * 0.1 + 0.5}s`;
            });

            // Animation des icônes des IA
            const aiIcons = document.querySelectorAll('.ai-icon');
            aiIcons.forEach(icon => {
                icon.addEventListener('mouseenter', () => {
                    icon.classList.add('animate-tada');
                });
                icon.addEventListener('animationend', () => {
                    icon.classList.remove('animate-tada');
                });
            });
        });
    </script>
</x-app-layout>
