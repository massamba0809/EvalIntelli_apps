<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'domain_id',
        'content',
    ];

    /**
     * Relation avec l'utilisateur qui a posé la question
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le domaine de la question
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * Relation avec les réponses des IA
     */
    public function iaResponses(): HasMany
    {
        return $this->hasMany(IaResponse::class);
    }

    /**
     * Relation avec l'évaluation (une seule par question)
     */
    public function evaluation(): HasOne
    {
        return $this->hasOne(Evaluation::class);
    }

    /**
     * Vérifie si la question appartient au domaine "Programmation"
     * LOGIQUE CORRIGÉE : PRIORITÉ ABSOLUE AU DOMAINE CHOISI
     */
    public function isProgrammingQuestion(): bool
    {
        if (!$this->domain) {
            return false;
        }

        // 🎯 PRIORITÉ ABSOLUE : Vérification STRICTE par nom de domaine choisi
        $domainName = strtolower($this->domain->name);
        $domainSlug = strtolower($this->domain->slug ?? '');

        // Mots-clés EXPLICITES pour programmation
        $programmingDomains = [
            'programmation', 'programming', 'code', 'coding',
            'développement', 'development', 'informatique',
            'web', 'software', 'logiciel', 'application'
        ];

        // Si le domaine contient ces mots, c'est DÉFINITIVEMENT de la programmation
        foreach ($programmingDomains as $keyword) {
            if (str_contains($domainName, $keyword) || str_contains($domainSlug, $keyword)) {
                \Log::info('Question PROGRAMMATION détectée par domaine choisi', [
                    'question_id' => $this->id,
                    'domain_name' => $this->domain->name,
                    'domain_slug' => $this->domain->slug,
                    'keyword_matched' => $keyword
                ]);
                return true;
            }
        }

        // 🎯 LOGIQUE CORRIGÉE : Si le domaine choisi n'est PAS programmation, retourner false
        \Log::info('Question NON-PROGRAMMATION : domaine choisi différent', [
            'question_id' => $this->id,
            'domain_name' => $this->domain->name,
            'domain_slug' => $this->domain->slug
        ]);

        return false;
    }

    /**
     * Vérifie si la question appartient au domaine "Mathématiques"
     * LOGIQUE CORRIGÉE : PRIORITÉ ABSOLUE AU DOMAINE CHOISI
     */
    public function isMathematicalQuestion(): bool
    {
        if (!$this->domain) {
            return false;
        }

        // 🎯 PRIORITÉ ABSOLUE : Vérification STRICTE par nom de domaine choisi
        $domainName = strtolower($this->domain->name);
        $domainSlug = strtolower($this->domain->slug ?? '');

        // Mots-clés EXPLICITES pour mathématiques
        $mathDomains = [
            'mathématiques', 'mathematics', 'math', 'maths',
            'logique', 'logic', 'calcul', 'calculation',
            'algèbre', 'algebra', 'géométrie', 'geometry'
        ];

        // Si le domaine contient ces mots, c'est DÉFINITIVEMENT des mathématiques
        foreach ($mathDomains as $keyword) {
            if (str_contains($domainName, $keyword) || str_contains($domainSlug, $keyword)) {
                \Log::info('Question MATHÉMATIQUES détectée par domaine choisi', [
                    'question_id' => $this->id,
                    'domain_name' => $this->domain->name,
                    'domain_slug' => $this->domain->slug,
                    'keyword_matched' => $keyword
                ]);
                return true;
            }
        }

        // 🎯 LOGIQUE CORRIGÉE : Si le domaine choisi n'est PAS mathématiques, retourner false
        \Log::info('Question NON-MATHÉMATIQUES : domaine choisi différent', [
            'question_id' => $this->id,
            'domain_name' => $this->domain->name,
            'domain_slug' => $this->domain->slug
        ]);

        return false;
    }

    public function isTranslationQuestion(): bool
    {
        if (!$this->domain) {
            return false;
        }

        // 🎯 PRIORITÉ ABSOLUE : Vérification STRICTE par nom de domaine choisi
        $domainName = strtolower($this->domain->name);
        $domainSlug = strtolower($this->domain->slug ?? '');

        // Mots-clés EXPLICITES pour traduction
        $translationDomains = [
            'traduction', 'translation', 'translate', 'traduire',
            'linguistique', 'linguistics', 'langues', 'languages',
            'langue étrangère', 'foreign language'
        ];

        // Si le domaine contient ces mots, c'est DÉFINITIVEMENT de la traduction
        foreach ($translationDomains as $keyword) {
            if (str_contains($domainName, $keyword) || str_contains($domainSlug, $keyword)) {
                \Log::info('Question TRADUCTION détectée par domaine choisi', [
                    'question_id' => $this->id,
                    'domain_name' => $this->domain->name,
                    'domain_slug' => $this->domain->slug,
                    'keyword_matched' => $keyword
                ]);
                return true;
            }
        }

        // 🎯 LOGIQUE CORRIGÉE : Si le domaine choisi n'est PAS traduction, retourner false
        \Log::info('Question NON-TRADUCTION : domaine choisi différent', [
            'question_id' => $this->id,
            'domain_name' => $this->domain->name,
            'domain_slug' => $this->domain->slug
        ]);

        return false;
    }

    /**
     * Vérifie si une question est évaluable (programmation OU mathématiques)
     * AVEC LOGIQUE EXCLUSIVE
     */
    public function isEvaluableQuestion(): bool
    {
        $evaluationType = $this->getEvaluationType();

        // Une question est évaluable si elle appartient à un domaine supporté
        $isEvaluable = in_array($evaluationType, ['programming', 'mathematics', 'translation', 'chemistry']);

        \Log::info('🔍 VÉRIFICATION ÉVALUABILITÉ', [
            'question_id' => $this->id,
            'domain_name' => $this->domain->name ?? 'N/A',
            'evaluation_type' => $evaluationType,
            'is_evaluable' => $isEvaluable
        ]);

        return $isEvaluable;
    }

    /**
     * Détermine le type d'évaluation nécessaire
     * LOGIQUE EXCLUSIVE CORRIGÉE
     */
    public function getEvaluationType(): string
    {
        if (!$this->domain) {
            return 'none';
        }

        // 🎯 PRIORITÉ ABSOLUE : Basé uniquement sur le domaine choisi par l'utilisateur
        // L'ordre de priorité ne compte plus car chaque domaine est exclusif

        if ($this->isTranslationQuestion()) {
            \Log::info('✅ Type d\'évaluation détecté : TRADUCTION', [
                'question_id' => $this->id,
                'domain_name' => $this->domain->name ?? 'N/A',
                'domain_slug' => $this->domain->slug ?? 'N/A'
            ]);
            return 'translation';
        }

        if ($this->isMathematicalQuestion()) {
            \Log::info('✅ Type d\'évaluation détecté : MATHÉMATIQUES', [
                'question_id' => $this->id,
                'domain_name' => $this->domain->name ?? 'N/A',
                'domain_slug' => $this->domain->slug ?? 'N/A'
            ]);
            return 'mathematics';
        }

        if ($this->isProgrammingQuestion()) {
            \Log::info('✅ Type d\'évaluation détecté : PROGRAMMATION', [
                'question_id' => $this->id,
                'domain_name' => $this->domain->name ?? 'N/A',
                'domain_slug' => $this->domain->slug ?? 'N/A'
            ]);
            return 'programming';
        }

        if ($this->isChemistryQuestion()) {
            \Log::info('✅ Type d\'évaluation détecté : CHIMIE', [
                'question_id' => $this->id,
                'domain_name' => $this->domain->name ?? 'N/A',
                'domain_slug' => $this->domain->slug ?? 'N/A'
            ]);
            return 'chemistry';
        }

        // Par défaut : non évaluable
        \Log::info('⚠️ Type d\'évaluation détecté : AUCUN (domaine non supporté)', [
            'question_id' => $this->id,
            'domain_name' => $this->domain->name ?? 'N/A',
            'domain_slug' => $this->domain->slug ?? 'N/A'
        ]);
        return 'none';
    }
    /**
     * MÉTHODE CORRIGÉE : Détection spécifique du contenu de programmation
     * Plus stricte pour éviter les faux positifs
     */
    protected function hasProgrammingContent(string $content): bool
    {
        $programmingKeywords = [
            // Langages de programmation EXPLICITES
            'python', 'javascript', 'java', 'php', 'c++', 'c#', 'ruby', 'go', 'rust',
            'html', 'css', 'sql', 'bash', 'shell', 'kotlin', 'swift', 'typescript',

            // Concepts de programmation SPÉCIFIQUES
            'classe', 'class', 'objet', 'object', 'méthode', 'method',
            'fonction', 'function', 'variable', 'array', 'tableau',
            'boucle', 'loop', 'condition', 'algorithme', 'algorithm',
            'récursion', 'recursion', 'iteration',

            // Outils et frameworks EXPLICITES
            'framework', 'api', 'database', 'serveur', 'server',
            'git', 'github', 'docker', 'kubernetes', 'laravel', 'react', 'vue',

            // Syntaxe de code SPÉCIFIQUE
            'def ', 'function ', 'class ', 'import ', 'require',
            'console.log', 'print(', 'echo ', 'return', 'var ', 'let ', 'const ',

            // Concepts web et développement
            'frontend', 'backend', 'fullstack', 'responsive',
            'mvc', 'crud', 'rest', 'json', 'xml', 'debugging',
            'compilation', 'interpreteur', 'librairie', 'package'
        ];

        $contentLower = strtolower($content);

        // Compter les mots-clés de programmation trouvés
        $programmingMatches = 0;
        foreach ($programmingKeywords as $keyword) {
            if (str_contains($contentLower, $keyword)) {
                $programmingMatches++;
            }
        }

        // Vérifier les patterns de code EXPLICITES
        $codePatterns = [
            '/\{\s*[\w\s\(\)]+\s*\}/',          // Accolades avec contenu
            '/\$[a-zA-Z_]\w*/',                 // Variables PHP
            '/def\s+\w+\s*\(/',                 // Fonctions Python
            '/function\s+\w+\s*\(/',            // Fonctions JS
            '/class\s+\w+\s*[:\{]/',            // Déclarations de classe
            '/import\s+\w+/',                   // Imports
            '/console\.log\s*\(/',              // Console.log
            '/\w+\(\s*\w*\s*\)/',              // Appels de fonction
            '/<\w+[^>]*>/',                     // Balises HTML
            '/\/\/.*|\/\*.*\*\//',             // Commentaires de code
        ];

        $codePatternMatches = 0;
        foreach ($codePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $codePatternMatches++;
            }
        }

        // C'est de la programmation si :
        // - Au moins 2 mots-clés de programmation OU
        // - Au moins 1 pattern de code clair
        $isProgramming = ($programmingMatches >= 2) || ($codePatternMatches >= 1);

        if ($isProgramming) {
            \Log::info('Contenu programmation détecté', [
                'programming_matches' => $programmingMatches,
                'code_pattern_matches' => $codePatternMatches,
                'content_preview' => Str::limit($content, 100)
            ]);
        }

        return $isProgramming;
    }

    /**
     * MÉTHODE CORRIGÉE : Détection mathématique plus stricte
     * Évite les conflits avec la programmation
     */
    protected function hasMathematicalContent(string $content): bool
    {
        // Mots-clés STRICTEMENT mathématiques (sans ambiguïté avec la programmation)
        $strictMathKeywords = [
            // Opérations mathématiques SPÉCIFIQUES
            'calculer', 'calcul', 'résoudre', 'solution mathématique',
            'équation', 'inéquation', 'système d\'équations',

            // Domaines mathématiques SPÉCIFIQUES
            'algèbre', 'géométrie', 'trigonométrie', 'logarithme',
            'dérivée', 'intégrale', 'limite', 'fonction mathématique',
            'matrice', 'vecteur', 'probabilité', 'statistique',
            'arithmétique', 'combinatoire', 'factorielle',

            // Termes mathématiques SANS AMBIGUÏTÉ
            'théorème', 'lemme', 'axiome', 'preuve mathématique',
            'démonstration', 'conjecture',

            // Fonctions mathématiques SPÉCIFIQUES
            'sinus', 'cosinus', 'tangente', 'exponentielle',
            'logarithme népérien', 'arctangente', 'racine carrée',

            // Concepts numériques SPÉCIFIQUES
            'nombre premier', 'nombre entier', 'nombre réel', 'nombre complexe',
            'fraction', 'décimal', 'pourcentage de',

            // Expressions mathématiques CLAIRES
            'plus grand que', 'plus petit que', 'égal à',
            'somme de', 'produit de', 'quotient de',
            'racine de', 'puissance de', 'carré de', 'cube de'
        ];

        $contentLower = strtolower($content);

        // Compter les mots-clés strictement mathématiques
        $mathMatches = 0;
        foreach ($strictMathKeywords as $keyword) {
            if (str_contains($contentLower, $keyword)) {
                $mathMatches++;
            }
        }

        // Vérifier les symboles mathématiques SPÉCIFIQUES
        $mathSymbols = ['≠', '≈', '≤', '≥', '√', '²', '³', '°', 'π', 'Σ', '∑', '∫', '∞', '∆', 'α', 'β', 'γ', 'θ'];
        $symbolMatches = 0;
        foreach ($mathSymbols as $symbol) {
            if (str_contains($content, $symbol)) {
                $symbolMatches++;
            }
        }

        // Patterns mathématiques SPÉCIFIQUES (éviter les conflits avec le code)
        $mathPatterns = [
            '/\d+\s*[\+\-\*\/\^]\s*\d+\s*=/',  // Équations avec égalité
            '/\d+\/\d+/',                       // Fractions
            '/\d+[eE][\+\-]?\d+/',             // Notation scientifique
            '/\(\s*\d+[\+\-\*\/]\d+\s*\)/',    // Expressions entre parenthèses
            '/x\s*[\+\-\*\/\^]\s*\d+/',        // Expressions algébriques
            '/\d+\s*x\s*[\+\-]/',              // Expressions linéaires
        ];

        $mathPatternMatches = 0;
        foreach ($mathPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $mathPatternMatches++;
            }
        }

        // C'est des mathématiques si :
        // - Au moins 1 mot-clé strictement mathématique OU
        // - Au moins 1 symbole mathématique OU
        // - Au moins 1 pattern mathématique
        $isMathematical = ($mathMatches >= 1) || ($symbolMatches >= 1) || ($mathPatternMatches >= 1);

        if ($isMathematical) {
            \Log::info('Contenu mathématique détecté', [
                'math_matches' => $mathMatches,
                'symbol_matches' => $symbolMatches,
                'math_pattern_matches' => $mathPatternMatches,
                'content_preview' => Str::limit($content, 100)
            ]);
        }

        return $isMathematical;
    }


    // ... (garder toutes les autres méthodes existantes inchangées)

    /**
     * Vérifie si la question a une évaluation complète
     */
    public function hasCompleteEvaluation(): bool
    {
        return $this->evaluation && $this->evaluation->isComplete();
    }

    /**
     * Obtient les réponses des IA organisées par modèle avec mapping correct
     */
    public function getResponsesByModel(): array
    {
        $responses = [];

        // Mapping des noms de modèles complets vers les clés courtes
        $modelMapping = [
            'openai/gpt-4o' => 'gpt4',
            'deepseek/deepseek-r1' => 'deepseek',
            'qwen/qwen-2.5-72b-instruct' => 'qwen'
        ];

        foreach ($this->iaResponses as $response) {
            $modelKey = $modelMapping[$response->model_name] ?? $this->normalizeModelName($response->model_name);
            $responses[$modelKey] = $response;
        }

        return $responses;
    }

    /**
     * Normalise le nom du modèle pour correspondre aux clés d'évaluation
     */
    protected function normalizeModelName(string $modelName): string
    {
        return match($modelName) {
            'openai/gpt-4o' => 'gpt4',
            'deepseek/deepseek-r1' => 'deepseek',
            'qwen/qwen-2.5-72b-instruct' => 'qwen',
            default => strtolower(str_replace(['/', '-'], ['_', '_'], $modelName))
        };
    }

    /**
     * Obtient les statistiques des réponses pour cette question
     */
    public function getResponsesStatsAttribute(): array
    {
        $responses = $this->iaResponses;

        return [
            'total' => $responses->count(),
            'average_tokens' => $responses->whereNotNull('token_usage')->avg('token_usage'),
            'average_response_time' => $responses->whereNotNull('response_time')->avg('response_time'),
            'models_used' => $responses->pluck('model_name')->unique()->values()->toArray()
        ];
    }

    /**
     * Vérifie si toutes les réponses IA sont disponibles
     */
    public function hasAllAIResponses(): bool
    {
        $expectedModels = ['gpt4', 'deepseek', 'qwen'];
        $actualModels = array_keys($this->getResponsesByModel());

        return count(array_intersect($expectedModels, $actualModels)) === 3;
    }

    /**
     * Obtient le temps de réponse le plus rapide
     */
    public function getFastestResponseTimeAttribute(): ?float
    {
        return $this->iaResponses->whereNotNull('response_time')->min('response_time');
    }

    /**
     * Obtient le temps de réponse le plus lent
     */
    public function getSlowestResponseTimeAttribute(): ?float
    {
        return $this->iaResponses->whereNotNull('response_time')->max('response_time');
    }

    /**
     * Vérifie si la question nécessite une évaluation automatique
     */
    public function needsAutomaticEvaluation(): bool
    {
        return $this->isEvaluableQuestion() &&
            $this->hasAllAIResponses() &&
            !$this->hasCompleteEvaluation();
    }

    /**
     * Obtient un résumé de la question pour les logs
     */
    public function getSummaryAttribute(): string
    {
        $content = Str::limit($this->content, 100);
        $type = $this->getEvaluationType();
        return "Question #{$this->id}: {$content} (Domain: {$this->domain?->name}, Type: {$type})";
    }

    /**
     * Obtient le statut d'évaluation de la question
     */
    public function getEvaluationStatusAttribute(): string
    {
        if (!$this->isEvaluableQuestion()) {
            return 'not_evaluable';
        }

        if ($this->evaluation) {
            return $this->evaluation->isComplete() ? 'completed' : 'partial';
        }

        if ($this->hasAllAIResponses()) {
            return 'pending';
        }

        return 'waiting_responses';
    }

    /**
     * Scope pour les questions de programmation
     */
    public function scopeProgramming($query)
    {
        return $query->whereHas('domain', function($q) {
            $q->where(function($query) {
                $query->where('name', 'LIKE', '%programmation%')
                    ->orWhere('name', 'LIKE', '%code%')
                    ->orWhere('name', 'LIKE', '%développement%')
                    ->orWhere('name', 'LIKE', '%programming%')
                    ->orWhere('name', 'LIKE', '%coding%')
                    ->orWhere('slug', 'LIKE', '%programming%')
                    ->orWhere('slug', 'LIKE', '%coding%');
            });
        });
    }

    /**
     * Scope pour les questions mathématiques
     */
    public function scopeMathematics($query)
    {
        return $query->whereHas('domain', function($q) {
            $q->where(function($query) {
                $query->where('name', 'LIKE', '%mathématiques%')
                    ->orWhere('name', 'LIKE', '%math%')
                    ->orWhere('name', 'LIKE', '%logique%')
                    ->orWhere('name', 'LIKE', '%mathematics%')
                    ->orWhere('name', 'LIKE', '%logic%')
                    ->orWhere('slug', 'LIKE', '%math%')
                    ->orWhere('slug', 'LIKE', '%logic%');
            });
        });
    }

    /**
     * Scope pour les questions évaluables (programmation OU mathématiques)
     */
    public function scopeEvaluable($query)
    {
        return $query->where(function($q) {
            $q->whereHas('domain', function($query) {
                $query->where(function($subQuery) {
                    // Programmation
                    $subQuery->where('name', 'LIKE', '%programmation%')
                        ->orWhere('name', 'LIKE', '%code%')
                        ->orWhere('name', 'LIKE', '%développement%')
                        ->orWhere('name', 'LIKE', '%programming%')
                        ->orWhere('name', 'LIKE', '%coding%')
                        ->orWhere('slug', 'LIKE', '%programming%')
                        ->orWhere('slug', 'LIKE', '%coding%')
                        // Mathématiques
                        ->orWhere('name', 'LIKE', '%mathématiques%')
                        ->orWhere('name', 'LIKE', '%math%')
                        ->orWhere('name', 'LIKE', '%logique%')
                        ->orWhere('name', 'LIKE', '%mathematics%')
                        ->orWhere('name', 'LIKE', '%logic%')
                        ->orWhere('slug', 'LIKE', '%math%')
                        ->orWhere('slug', 'LIKE', '%logic%');
                });
            });
        });
    }

    /**
     * Scope pour les questions avec toutes les réponses IA
     */
    public function scopeWithAllResponses($query)
    {
        return $query->whereHas('iaResponses', function($q) {
            $q->whereIn('model_name', [
                'openai/gpt-4o',
                'deepseek/deepseek-r1',
                'qwen/qwen-2.5-72b-instruct'
            ]);
        }, '=', 3);
    }

    /**
     * Scope pour les questions sans évaluation
     */
    public function scopeWithoutEvaluation($query)
    {
        return $query->doesntHave('evaluation');
    }

    /**
     * Scope pour les questions qui nécessitent une évaluation
     */
    public function scopeNeedsEvaluation($query)
    {
        return $query->evaluable()
            ->withAllResponses()
            ->withoutEvaluation();
    }

    /**
     * Scope pour les questions par type d'évaluation
     */
    public function scopeByEvaluationType($query, string $type)
    {
        switch ($type) {
            case 'mathematics':
                return $query->mathematics();
            case 'programming':
                return $query->programming();
            case 'evaluable':
                return $query->evaluable();
            default:
                return $query;
        }
    }

    /**
     * Obtient les tags associés à la question
     */
    /**
     * 🔧 MISE À JOUR : Obtient les tags associés à la question AVEC TRADUCTION
     */
    public function getTagsAttribute(): array
    {
        $tags = [];

        // Tag du domaine
        if ($this->domain) {
            $tags[] = $this->domain->name;
        }

        // Tags selon le type
        if ($this->isTranslationQuestion()) {
            $tags[] = 'Traduction';
            $tags[] = 'Évaluable';
        } elseif ($this->isMathematicalQuestion()) {
            $tags[] = 'Mathématiques';
            $tags[] = 'Évaluable';
        } elseif ($this->isProgrammingQuestion()) {
            $tags[] = 'Programmation';
            $tags[] = 'Évaluable';
        } else {
            $tags[] = 'Question générale';
        }

        // Tag d'évaluation
        if ($this->evaluation) {
            $tags[] = 'Évaluée';
        } elseif ($this->isEvaluableQuestion() && $this->hasAllAIResponses()) {
            $tags[] = 'Prête pour évaluation';
        }

        // Tag selon le nombre de réponses
        $responseCount = $this->iaResponses->count();
        if ($responseCount >= 3) {
            $tags[] = 'Complète';
        } elseif ($responseCount > 0) {
            $tags[] = 'Partielle';
        } else {
            $tags[] = 'Sans réponse';
        }

        return array_unique($tags);
    }


    /**
     * Obtient une description du type de question
     */
    public function getTypeDescriptionAttribute(): string
    {
        if ($this->isTranslationQuestion()) {
            return 'Question de traduction linguistique';
        } elseif ($this->isMathematicalQuestion()) {
            return 'Question de mathématiques ou logique';
        } elseif ($this->isProgrammingQuestion()) {
            return 'Question de programmation';
        } else {
            return 'Question générale';
        }
    }

    public function scopeTranslation($query)
    {
        return $query->whereHas('domain', function($q) {
            $q->where(function($query) {
                $query->where('name', 'LIKE', '%traduction%')
                    ->orWhere('name', 'LIKE', '%translation%')
                    ->orWhere('name', 'LIKE', '%translate%')
                    ->orWhere('name', 'LIKE', '%langues%')
                    ->orWhere('name', 'LIKE', '%linguistics%')
                    ->orWhere('slug', 'LIKE', '%translation%')
                    ->orWhere('slug', 'LIKE', '%translate%');
            });
        });
    }


    protected function hasTranslationContent(string $content): bool
    {
        $translationKeywords = [
            // Mots-clés français EXPLICITES
            'traduire', 'traduisez', 'traduction', 'traduis',
            'en français', 'en anglais', 'en espagnol', 'en allemand',
            'vers le français', 'vers l\'anglais',

            // Mots-clés anglais EXPLICITES
            'translate', 'translation', 'translate to', 'translate into',
            'into french', 'into english', 'into spanish',
            'from french', 'from english',

            // Patterns de langues SPÉCIFIQUES
            'français-anglais', 'anglais-français',
            'french-english', 'english-french',
            'spanish-english', 'german-french',

            // Expressions courantes de traduction
            'comment dit-on', 'comment dire en',
            'what does it mean in', 'how do you say',
            'que signifie en', 'que veut dire en'
        ];

        $contentLower = strtolower($content);

        // Compter les mots-clés de traduction trouvés
        $translationMatches = 0;
        foreach ($translationKeywords as $keyword) {
            if (str_contains($contentLower, $keyword)) {
                $translationMatches++;
            }
        }

        // Vérifier les patterns de traduction EXPLICITES
        $translationPatterns = [
            '/traduire?\s*[:]\s*.+/i',                  // "Traduire: Hello"
            '/translate\s+to\s+\w+\s*[:]\s*.+/i',      // "Translate to French: Hello"
            '/en\s+\w+\s*[:]\s*.+/i',                  // "En français: Hello"
            '/\w+\s+to\s+\w+\s*[:]\s*.+/i',           // "English to French: Hello"
            '/comment\s+dit-on\s*.+/i',                // "Comment dit-on..."
            '/que\s+signifie\s*.+/i',                  // "Que signifie..."
            '/how\s+do\s+you\s+say\s*.+/i',           // "How do you say..."
            '/what\s+does\s*.+\s+mean\s+in\s+\w+/i',  // "What does X mean in French"
        ];

        $translationPatternMatches = 0;
        foreach ($translationPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $translationPatternMatches++;
            }
        }

        // C'est de la traduction si :
        // - Au moins 1 mot-clé de traduction ET 1 pattern OU
        // - Au moins 2 mots-clés de traduction
        $isTranslation = (($translationMatches >= 1) && ($translationPatternMatches >= 1)) ||
            ($translationMatches >= 2);

        if ($isTranslation) {
            \Log::info('Contenu traduction détecté', [
                'translation_matches' => $translationMatches,
                'pattern_matches' => $translationPatternMatches,
                'content_preview' => Str::limit($content, 100)
            ]);
        }

        return $isTranslation;
    }





    public function testDeepLWithQuestionText()
    {
        try {
            $deepL = app(\App\Services\DeepLService::class);

            // Extraire le texte à traduire de la question
            $content = $this->content;

            // Patterns pour extraire le texte source
            $patterns = [
                '/translate\s+to\s+\w+\s*:\s*(.+)/i',
                '/traduire?\s*[:]\s*(.+)/i',
                '/en\s+\w+\s*[:]\s*(.+)/i'
            ];

            $sourceText = null;
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content, $matches)) {
                    $sourceText = trim($matches[1]);
                    break;
                }
            }

            if (!$sourceText) {
                $sourceText = $content; // Fallback
            }

            // Test avec les mêmes paramètres que l'évaluation
            return $deepL->translate($sourceText, 'FR', 'auto');

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }


    public function isChemistryQuestion(): bool
    {
        if (!$this->domain) {
            return false;
        }

        // 🎯 PRIORITÉ ABSOLUE : Vérification STRICTE par nom de domaine choisi
        $domainName = strtolower($this->domain->name);
        $domainSlug = strtolower($this->domain->slug ?? '');

        // Mots-clés EXPLICITES pour chimie
        $chemistryDomains = [
            'chimie', 'chemistry', 'chemical', 'chimique',
            'biologie', 'biology', 'physique', 'physics',
            'sciences', 'science'
        ];

        // Si le domaine contient ces mots, c'est DÉFINITIVEMENT de la chimie
        foreach ($chemistryDomains as $keyword) {
            if (str_contains($domainName, $keyword) || str_contains($domainSlug, $keyword)) {
                \Log::info('Question CHIMIE détectée par domaine choisi', [
                    'question_id' => $this->id,
                    'domain_name' => $this->domain->name,
                    'domain_slug' => $this->domain->slug,
                    'keyword_matched' => $keyword
                ]);
                return true;
            }
        }

        // 🎯 LOGIQUE CORRIGÉE : Si le domaine choisi n'est PAS chimie, retourner false
        \Log::info('Question NON-CHIMIE : domaine choisi différent', [
            'question_id' => $this->id,
            'domain_name' => $this->domain->name,
            'domain_slug' => $this->domain->slug
        ]);

        return false;
    }

    private function getDetectionReason($score, $reactionMatches, $formulaMatches, $keywordMatches): string
    {
        if ($score >= 8) return 'score_elevé';
        if ($reactionMatches >= 1) return 'équation_chimique';
        if ($formulaMatches >= 2) return 'formules_multiples';
        if ($keywordMatches >= 1 && $formulaMatches >= 1) return 'mots-clés_+_formules';
        if ($keywordMatches >= 2) return 'mots-clés_multiples';
        return 'unknown';
    }




    public function forceEvaluationTypeByDomain(): string
    {
        if (!$this->domain) {
            return 'none';
        }

        $domainName = strtolower($this->domain->name);
        $domainSlug = strtolower($this->domain->slug ?? '');

        // Traduction
        if (str_contains($domainName, 'traduction') || str_contains($domainSlug, 'traduction')) {
            return 'translation';
        }

        // Mathématiques
        if (str_contains($domainName, 'math') || str_contains($domainName, 'logique') ||
            str_contains($domainSlug, 'math') || str_contains($domainSlug, 'logique')) {
            return 'mathematics';
        }

        // Programmation
        if (str_contains($domainName, 'programmation') || str_contains($domainName, 'programming') ||
            str_contains($domainSlug, 'programmation') || str_contains($domainSlug, 'programming')) {
            return 'programming';
        }

        return 'none';
    }

}
