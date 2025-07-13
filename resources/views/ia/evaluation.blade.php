{{-- resources/views/ia/evaluation.blade.php - VERSION AVEC SUPPORT TRADUCTION --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Évaluation - Question #') }}{{ $question->id }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="container mx-auto px-4">

                <!-- En-tête adaptatif selon le type d'évaluation -->
                <div class="bg-gradient-to-r {{
                    $evaluation_type === 'mathematics' ? 'from-purple-600 to-indigo-600' :
                    ($evaluation_type === 'translation' ? 'from-green-600 to-blue-600' : 'from-blue-600 to-purple-600')
                }} text-white rounded-lg p-6 mb-8">
                    <h1 class="text-3xl font-bold mb-2">
                        {{ $evaluation_type === 'mathematics' ? '🧮' : ($evaluation_type === 'translation' ? '🌐' : '💻') }}
                        {{
                            $evaluation_type === 'mathematics' ? 'Évaluation Mathématique' :
                            ($evaluation_type === 'translation' ? 'Évaluation de Traduction' : 'Évaluation de Programmation')
                        }}
                    </h1>
                    <p class="text-blue-100">
                        {{
                            $evaluation_type === 'mathematics' ? 'Analyse automatique avec référence Wolfram Alpha' :
                            ($evaluation_type === 'translation' ? 'Analyse automatique avec référence DeepL' : 'Analyse automatique de la qualité du code')
                        }}
                    </p>
                </div>

                <!-- Question originale -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                        💭 Question analysée
                    </h3>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <p class="text-gray-800 dark:text-gray-200">{{ $question->content }}</p>
                    </div>
                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Domaine: <span class="font-medium">{{ $question->domain->name }}</span> |
                        Type: <span class="font-medium">{{
                            $evaluation_type === 'mathematics' ? 'Mathématiques' :
                            ($evaluation_type === 'translation' ? 'Traduction' : 'Programmation')
                        }}</span> |
                        Posée le: {{ $question->created_at->format('d/m/Y à H:i') }}
                    </div>
                </div>

                @if($evaluation)
                    <!-- Référence Wolfram Alpha (mathématiques uniquement) -->
                    @if($evaluation_type === 'mathematics')
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                🔬 Référence Wolfram Alpha
                                @if($evaluation->hasWolframReference())
                                    <span class="ml-2 px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs rounded-full">Disponible</span>
                                @else
                                    <span class="ml-2 px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 text-xs rounded-full">Non disponible</span>
                                @endif
                            </h3>

                            @if($evaluation->hasWolframReference())
                                <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-4 mb-4">
                                    <div class="font-mono text-sm whitespace-pre-wrap text-gray-800 dark:text-gray-200">{{ $evaluation->wolfram_reference }}</div>
                                </div>
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 space-x-4">
                                    <span>⏱️ Temps de réponse: {{ number_format($evaluation->wolfram_response_time ?? 0, 2) }}s</span>
                                    <span>📏 Longueur: {{ strlen($evaluation->wolfram_reference) }} caractères</span>
                                </div>
                            @else
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                                    <p class="text-yellow-800 dark:text-yellow-200">
                                        ⚠️ Wolfram Alpha n'a pas pu fournir de référence pour cette question.
                                        L'évaluation se base uniquement sur la justesse mathématique et la cohérence des réponses.
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Référence DeepL (traductions uniquement) -->
                    @if($evaluation_type === 'translation')
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                🌐 Référence DeepL
                                @if(isset($evaluation->deepl_reference) && !empty($evaluation->deepl_reference))
                                    <span class="ml-2 px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs rounded-full">Disponible</span>
                                @else
                                    <span class="ml-2 px-2 py-1 bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 text-xs rounded-full">Non disponible</span>
                                @endif
                            </h3>

                            @if(isset($evaluation->deepl_reference) && !empty($evaluation->deepl_reference))
                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4 mb-4">
                                    <div class="font-mono text-sm whitespace-pre-wrap text-gray-800 dark:text-gray-200">{{ $evaluation->deepl_reference }}</div>
                                </div>
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400 space-x-4">
                                    <span>⏱️ Temps de réponse: {{ number_format($evaluation->deepl_response_time ?? 0, 2) }}s</span>
                                    <span>📏 Longueur: {{ strlen($evaluation->deepl_reference) }} caractères</span>
                                    @if(isset($evaluation->translation_data) && is_array($evaluation->translation_data))
                                        <span>🔄 {{ $evaluation->translation_data['source_language'] ?? 'AUTO' }} → {{ $evaluation->translation_data['target_language'] ?? 'FR' }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                                    <p class="text-yellow-800 dark:text-yellow-200">
                                        ⚠️ DeepL n'a pas pu fournir de référence pour cette traduction.
                                        L'évaluation se base uniquement sur la qualité linguistique intrinsèque.
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Résultats de l'évaluation -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            🏆 Résultats de l'évaluation
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <!-- Meilleure IA -->
                            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4 text-center">
                                <div class="text-2xl mb-2">👑</div>
                                <div class="text-sm text-green-600 dark:text-green-400 font-medium">Meilleure IA</div>
                                <div class="text-lg font-bold text-green-800 dark:text-green-300">
                                    {{ $evaluation->best_ai_name }}
                                </div>
                            </div>

                            <!-- Scores individuels -->
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-4 text-center">
                                <div class="text-2xl mb-2">🤖</div>
                                <div class="text-sm text-blue-600 dark:text-blue-400 font-medium">GPT-4</div>
                                <div class="text-lg font-bold text-blue-800 dark:text-blue-300">
                                    {{ $evaluation->note_gpt4 ?? 'N/A' }}/10
                                </div>
                            </div>

                            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-700 rounded-lg p-4 text-center">
                                <div class="text-2xl mb-2">🧠</div>
                                <div class="text-sm text-purple-600 dark:text-purple-400 font-medium">DeepSeek</div>
                                <div class="text-lg font-bold text-purple-800 dark:text-purple-300">
                                    {{ $evaluation->note_deepseek ?? 'N/A' }}/10
                                </div>
                            </div>

                            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-700 rounded-lg p-4 text-center">
                                <div class="text-2xl mb-2">⚡</div>
                                <div class="text-sm text-orange-600 dark:text-orange-400 font-medium">Qwen</div>
                                <div class="text-lg font-bold text-orange-800 dark:text-orange-300">
                                    {{ $evaluation->note_qwen ?? 'N/A' }}/10
                                </div>
                            </div>
                        </div>

                        <!-- Score moyen -->
                        <div class="text-center">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Score moyen</div>
                            <div class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                                {{ number_format($evaluation->average_score, 1) }}/10
                            </div>
                        </div>
                    </div>

                    <!-- Analyse globale -->
                    @if($evaluation->commentaire_global)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
                                💬 Analyse globale
                                @if($evaluation_type === 'mathematics')
                                    par Claude
                                @elseif($evaluation_type === 'translation')
                                    par Claude
                                @else
                                    par Claude
                                @endif
                            </h3>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <p class="text-gray-800 dark:text-gray-200 leading-relaxed">{{ $evaluation->commentaire_global }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Évaluations détaillées par IA -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                        @foreach(['gpt4' => 'GPT-4 Omni', 'deepseek' => 'DeepSeek R1', 'qwen' => 'Qwen 2.5 72B'] as $aiKey => $aiName)
                            @php
                                $score = $evaluation->getScoreForModel($aiKey);
                                $details = $evaluation->getEvaluationDetails($aiKey);
                                $isBest = $evaluation->isBestAI($aiKey);
                            @endphp

                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden {{ $isBest ? 'ring-2 ring-green-500' : '' }}">
                                <!-- En-tête de l'IA -->
                                <div class="bg-gradient-to-r {{ $isBest ? 'from-green-500 to-green-600' : 'from-gray-500 to-gray-600' }} text-white p-4">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-lg">
                                            {{ $isBest ? '👑 ' : '' }}{{ $aiName }}
                                        </h4>
                                        <div class="bg-white bg-opacity-20 rounded-full px-3 py-1">
                                            <span class="font-bold text-lg">{{ $score ?? 'N/A' }}/10</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4">
                                    @if($details && is_array($details))
                                        <!-- Critères adaptés selon le type -->
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
                                                    <div>
                                                        <div class="flex justify-between text-sm mb-1">
                                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $criterionName }}</span>
                                                            <span class="text-gray-600 dark:text-gray-400">{{ $criterionScore }}/2</span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                            <div class="bg-purple-600 h-2 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                                                        </div>
                                                        @if(!empty($criterionAnalysis) && is_string($criterionAnalysis) && strlen($criterionAnalysis) > 10)
                                                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
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
                                                    <div>
                                                        <div class="flex justify-between text-sm mb-1">
                                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $criterionName }}</span>
                                                            <span class="text-gray-600 dark:text-gray-400">{{ $criterionScore }}/2</span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                            <div class="bg-green-600 h-2 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                                                        </div>
                                                        @if(!empty($criterionAnalysis) && is_string($criterionAnalysis) && strlen($criterionAnalysis) > 10)
                                                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
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
                                                    <div>
                                                        <div class="flex justify-between text-sm mb-1">
                                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $criterionName }}</span>
                                                            <span class="text-gray-600 dark:text-gray-400">{{ $criterionScore }}/2</span>
                                                        </div>
                                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $percentage }}%"></div>
                                                        </div>
                                                        @if(!empty($criterionAnalysis) && is_string($criterionAnalysis) && strlen($criterionAnalysis) > 10)
                                                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                                                {{ Str::limit($criterionAnalysis, 120) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>

                                        <!-- Hallucination (mathématiques et traductions) -->
                                        @if($evaluation_type === 'mathematics' || $evaluation_type === 'translation')
                                            @php
                                                $hallucinationAnalysis = $details['hallucination_analyse'] ?? null;
                                                if (empty($hallucinationAnalysis) && isset($details['hallucination']) && is_string($details['hallucination'])) {
                                                    $hallucinationAnalysis = $details['hallucination'];
                                                }
                                            @endphp
                                            @if(!empty($hallucinationAnalysis) && is_string($hallucinationAnalysis) && strlen($hallucinationAnalysis) > 10)
                                                <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                                        🔍 Détection d'hallucination :
                                                    </div>
                                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $hallucinationAnalysis }}</p>
                                                </div>
                                            @endif
                                        @endif

                                        <!-- Commentaire détaillé -->
                                        @if(isset($details['commentaire']) && !empty($details['commentaire']))
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Analyse détaillée :</div>
                                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $details['commentaire'] }}</p>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center text-gray-500 dark:text-gray-400 py-4">
                                            <div class="text-4xl mb-2">⚠️</div>
                                            <p>Détails d'évaluation non disponibles</p>
                                            <p class="text-xs mt-1">Les critères détaillés n'ont pas pu être récupérés</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Réponses originales -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{
                                $evaluation_type === 'mathematics' ? '🧮' :
                                ($evaluation_type === 'translation' ? '🌐' : '💻')
                            }} Réponses originales des IA
                        </h3>

                        <div class="space-y-6">
                            @foreach($responses as $aiKey => $response)
                                @php
                                    $aiNames = ['gpt4' => 'GPT-4 Omni', 'deepseek' => 'DeepSeek R1', 'qwen' => 'Qwen 2.5 72B'];
                                    $aiName = $aiNames[$aiKey] ?? $aiKey;
                                    $score = $evaluation->getScoreForModel($aiKey);
                                @endphp

                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center justify-between">
                                            <h4 class="font-medium text-gray-900 dark:text-white">{{ $aiName }}</h4>
                                            <div class="flex items-center space-x-2">
                                                <span class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs px-2 py-1 rounded">
                                                    Score: {{ $score ?? 'N/A' }}/10
                                                </span>
                                                @if($response->response_time)
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                                        {{ number_format($response->response_time, 2) }}s
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <div class="prose dark:prose-invert max-w-none">
                                            <pre class="whitespace-pre-wrap text-sm bg-gray-100 dark:bg-gray-800 p-3 rounded border overflow-x-auto"><code>{{ $response->cleaned_response ?? $response->response }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Métadonnées -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            📈 Métadonnées de l'évaluation
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-{{
                            $evaluation_type === 'mathematics' ? '4' :
                            ($evaluation_type === 'translation' ? '4' : '3')
                        }} gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Type d'évaluation</div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{
                                        $evaluation_type === 'mathematics' ? 'Mathématiques' :
                                        ($evaluation_type === 'translation' ? 'Traduction' : 'Programmation')
                                    }}
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Tokens utilisés</div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($evaluation->token_usage_evaluation ?? 0) }}
                                </div>
                            </div>

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Temps d'évaluation</div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $evaluation->response_time_evaluation ? number_format($evaluation->response_time_evaluation, 2) . 's' : 'N/A' }}
                                </div>
                            </div>

                            @if($evaluation_type === 'mathematics')
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Wolfram Alpha</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $evaluation->hasWolframReference() ? '✅ Utilisé' : '❌ Non disponible' }}
                                    </div>
                                </div>
                            @elseif($evaluation_type === 'translation')
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="text-sm text-gray-600 dark:text-gray-400">DeepL</div>
                                    <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ (isset($evaluation->deepl_reference) && !empty($evaluation->deepl_reference)) ? '✅ Utilisé' : '❌ Non disponible' }}
                                    </div>
                                </div>
                            @endif

                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="text-sm text-gray-600 dark:text-gray-400">Évaluée le</div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $evaluation->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>

                @else
                    <!-- Pas d'évaluation - ne devrait jamais arriver avec le système automatique -->
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-6 text-center">
                        <div class="text-4xl mb-4">❌</div>
                        <h3 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-2">
                            Évaluation manquante
                        </h3>
                        <p class="text-red-700 dark:text-red-300 mb-4">
                            Cette question n'a pas d'évaluation disponible. L'évaluation automatique a peut-être échoué.
                        </p>
                        <a href="{{ route('ia.results.by.id', ['question' => $question->id]) }}"
                           class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                            ← Retour aux résultats
                        </a>
                    </div>
                @endif

                <!-- Navigation -->
                <div class="flex justify-between mt-8">
                    <a href="{{ route('ia.index') }}"
                       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg transition-colors">
                        ← Retour à l'accueil
                    </a>

                    <div class="space-x-4">
                        @if($evaluation)
                            <form method="POST" action="{{ route('questions.reprocess', $question) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Êtes-vous sûr de vouloir relancer l\'évaluation ?')"
                                        class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-2 rounded-lg transition-colors">
                                    🔄 Relancer l'évaluation
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('ia.form', $question->domain) }}"
                           class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg transition-colors">
                            ➕ Nouvelle question
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Styles épurés pour la page d'évaluation */
        .prose pre {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.375rem;
            padding: 0.75rem;
            overflow-x: auto;
        }

        .dark .prose pre {
            background-color: #374151;
            border-color: #4b5563;
            color: #f9fafb;
        }

        /* Animation simple pour les barres de progression */
        .bg-blue-600, .bg-purple-600, .bg-green-600 {
            transition: width 0.5s ease-in-out;
        }

        /* Animation d'entrée douce pour les cartes */
        .bg-white.dark\:bg-gray-800 {
            animation: fadeInUp 0.4s ease-out;
        }

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

        /* Effet hover subtil pour les cartes */
        .bg-white.dark\:bg-gray-800:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .dark .bg-white.dark\:bg-gray-800:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        /* Mise en valeur de la meilleure IA */
        .ring-2.ring-green-500 {
            box-shadow: 0 0 0 2px #10b981, 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        /* Styles adaptatifs selon le type */
        .mathematics-theme {
            --primary-color: #8b5cf6;
            --secondary-color: #6366f1;
        }

        .programming-theme {
            --primary-color: #3b82f6;
            --secondary-color: #8b5cf6;
        }

        .translation-theme {
            --primary-color: #10b981;
            --secondary-color: #3b82f6;
        }

        /* Responsive design amélioré */
        @media (max-width: 768px) {
            .grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }

            .lg\:grid-cols-3 {
                grid-template-columns: 1fr;
            }

            .space-x-4 > * + * {
                margin-left: 0;
                margin-top: 0.5rem;
            }

            .space-x-4 {
                display: flex;
                flex-direction: column;
            }
        }
    </style>

    <script>
        // Script minimal pour la page d'évaluation
        document.addEventListener('DOMContentLoaded', function() {
            console.log('📊 Page d\'évaluation chargée');

            // Adaptation thématique selon le type
            const evaluationType = "{{ $evaluation_type }}";
            if (evaluationType === 'mathematics') {
                document.body.classList.add('mathematics-theme');
            } else if (evaluationType === 'translation') {
                document.body.classList.add('translation-theme');
            } else {
                document.body.classList.add('programming-theme');
            }

            // Animation d'entrée progressive pour les cartes
            const cards = document.querySelectorAll('.bg-white.dark\\:bg-gray-800');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });

        // Fonction pour copier le contenu d'une réponse (bonus)
        function copyResponseContent(button) {
            const responseContent = button.closest('.border').querySelector('pre code').textContent;
            navigator.clipboard.writeText(responseContent).then(() => {
                const originalText = button.textContent;
                button.textContent = '✅ Copié';
                setTimeout(() => {
                    button.textContent = originalText;
                }, 2000);
            });
        }
    </script>
</x-app-layout>
