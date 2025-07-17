<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'evaluation_type',
        'evaluation_gpt4',
        'evaluation_deepseek',
        'evaluation_qwen',
        'note_gpt4',
        'note_deepseek',
        'note_qwen',
        'meilleure_ia',
        'commentaire_global',
        'token_usage_evaluation',
        'response_time_evaluation',
        'wolfram_reference',
        'wolfram_response_time',
        'wolfram_status',

        'deepl_reference',
        'deepl_response_time',
        'deepl_status',
        'translation_data',
    ];

    protected $casts = [
        'note_gpt4' => 'integer',
        'note_deepseek' => 'integer',
        'note_qwen' => 'integer',
        'token_usage_evaluation' => 'integer',
        'response_time_evaluation' => 'decimal:3',
        'wolfram_response_time' => 'decimal:3',
    ];

    /**
     * Relation avec la question évaluée
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Vérifie si c'est une évaluation mathématique
     */
    public function isMathematicalEvaluation(): bool
    {
        return $this->evaluation_type === 'mathematics';
    }

    /**
     * Vérifie si c'est une évaluation de programmation
     */
    public function isProgrammingEvaluation(): bool
    {
        return $this->evaluation_type === 'programming';
    }

    /**
     * Vérifie si une référence Wolfram est disponible
     */
    public function hasWolframReference(): bool
    {
        return !is_null($this->wolfram_reference) && !empty(trim($this->wolfram_reference));
    }

    /**
     * Obtient le statut de Wolfram Alpha
     */
    public function getWolframStatusAttribute(): string
    {
        if (!$this->isMathematicalEvaluation()) {
            return 'not_applicable';
        }

        if ($this->hasWolframReference()) {
            return 'success';
        }

        return $this->attributes['wolfram_status'] ?? 'unavailable';
    }

    /**
     * Convertit les données JSON en tableau de manière sécurisée
     */
    private function parseJsonField($field): ?array
    {
        $value = $this->attributes[$field] ?? null;

        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }




    /**
     * Accesseur pour evaluation_gpt4
     */
    public function getEvaluationGpt4Attribute()
    {
        return $this->parseJsonField('evaluation_gpt4');
    }

    /**
     * Accesseur pour evaluation_deepseek
     */
    public function getEvaluationDeepseekAttribute()
    {
        return $this->parseJsonField('evaluation_deepseek');
    }

    /**
     * Accesseur pour evaluation_qwen
     */
    public function getEvaluationQwenAttribute()
    {
        return $this->parseJsonField('evaluation_qwen');
    }
    public function hasDeepLReference(): bool
    {
        return !is_null($this->deepl_reference) && !empty(trim($this->deepl_reference));
    }
    public function getDeepLStatusAttribute(): string
    {
        if (!$this->isTranslationEvaluation()) {
            return 'not_applicable';
        }

        if ($this->hasDeepLReference()) {
            return 'success';
        }

        return $this->attributes['deepl_status'] ?? 'unavailable';
    }

    public function getTranslationDataAttribute()
    {
        $value = $this->attributes['translation_data'] ?? null;

        if (is_null($value)) {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    public function setTranslationDataAttribute($value)
    {
        $this->attributes['translation_data'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * 🌐 NOUVEAU : Obtient les statistiques DeepL
     */
    public function getDeepLStatsAttribute(): array
    {
        return [
            'has_reference' => $this->hasDeepLReference(),
            'status' => $this->deepl_status,
            'response_time' => $this->deepl_response_time,
            'reference_length' => $this->deepl_reference ? strlen($this->deepl_reference) : 0,
            'translation_data' => $this->translation_data,
        ];
    }





    public function isTranslationEvaluation(): bool
    {
        return $this->evaluation_type === 'translation';
    }


    /**
     * Mutateur pour evaluation_gpt4
     */
    public function setEvaluationGpt4Attribute($value)
    {
        $this->attributes['evaluation_gpt4'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Mutateur pour evaluation_deepseek
     */
    public function setEvaluationDeepseekAttribute($value)
    {
        $this->attributes['evaluation_deepseek'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Mutateur pour evaluation_qwen
     */
    public function setEvaluationQwenAttribute($value)
    {
        $this->attributes['evaluation_qwen'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Obtient la note la plus élevée parmi les 3 IA
     */
    public function getBestScoreAttribute(): int
    {
        return max(
            $this->note_gpt4 ?? 0,
            $this->note_deepseek ?? 0,
            $this->note_qwen ?? 0
        );
    }

    /**
     * Obtient la note moyenne des 3 IA
     */
    public function getAverageScoreAttribute(): float
    {
        $scores = array_filter([
            $this->note_gpt4,
            $this->note_deepseek,
            $this->note_qwen
        ]);

        return count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0;
    }

    /**
     * Obtient la note la plus basse parmi les 3 IA
     */
    public function getWorstScoreAttribute(): int
    {
        $scores = array_filter([
            $this->note_gpt4,
            $this->note_deepseek,
            $this->note_qwen
        ]);

        return count($scores) > 0 ? min($scores) : 0;
    }

    /**
     * Obtient les notes formatées pour l'affichage
     */
    public function getFormattedScoresAttribute(): array
    {
        return [
            'GPT-4' => $this->note_gpt4 ?? 'N/A',
            'DeepSeek' => $this->note_deepseek ?? 'N/A',
            'Qwen' => $this->note_qwen ?? 'N/A'
        ];
    }

    /**
     * Obtient les scores classés par ordre décroissant
     */
    public function getScoresRankedAttribute(): array
    {
        $scores = [
            'gpt4' => ['name' => 'GPT-4', 'score' => $this->note_gpt4],
            'deepseek' => ['name' => 'DeepSeek', 'score' => $this->note_deepseek],
            'qwen' => ['name' => 'Qwen', 'score' => $this->note_qwen]
        ];

        // Filtrer les valeurs nulles
        $scores = array_filter($scores, function($item) {
            return !is_null($item['score']);
        });

        // Trier par score décroissant
        uasort($scores, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $scores;
    }

    /**
     * Vérifie si l'évaluation est complète
     */
    public function isComplete(): bool
    {
        return !is_null($this->note_gpt4) &&
            !is_null($this->note_deepseek) &&
            !is_null($this->note_qwen) &&
            !is_null($this->meilleure_ia);
    }

    /**
     * Obtient l'évaluation détaillée pour une IA spécifique
     */
    public function getEvaluationForAI(string $aiName): ?array
    {
        return match(strtolower($aiName)) {
            'gpt4', 'gpt-4', 'openai/gpt-4o' => $this->evaluation_gpt4,
            'deepseek', 'deepseek-r1', 'deepseek/deepseek-r1' => $this->evaluation_deepseek,
            'qwen', 'qwen-2.5', 'qwen/qwen-2.5-72b-instruct' => $this->evaluation_qwen,
            default => null
        };
    }

    /**
     * Obtient la note pour une IA spécifique
     */
    public function getScoreForAI(string $aiName): ?int
    {
        return match(strtolower($aiName)) {
            'gpt4', 'gpt-4', 'openai/gpt-4o' => $this->note_gpt4,
            'deepseek', 'deepseek-r1', 'deepseek/deepseek-r1' => $this->note_deepseek,
            'qwen', 'qwen-2.5', 'qwen/qwen-2.5-72b-instruct' => $this->note_qwen,
            default => null
        };
    }

    /**
     * Obtient les détails d'évaluation pour un modèle spécifique par clé courte
     */
    public function getEvaluationDetails(?string $model): ?array
    {


        if (is_null($model) || empty($model)) {
            return null;
        }
        return match($model) {
            'gpt4' => $this->evaluation_gpt4,
            'deepseek' => $this->evaluation_deepseek,
            'qwen' => $this->evaluation_qwen,
            default => null
        };
    }

    /**
     * Obtient la note pour un modèle spécifique par clé courte
     */
    public function getScoreForModel(?string $model): ?int
    {

        if (is_null($model) || empty($model)) {
            return null;
        }
        return match($model) {
            'gpt4' => $this->note_gpt4,
            'deepseek' => $this->note_deepseek,
            'qwen' => $this->note_qwen,
            default => null
        };
    }

    /**
     * Obtient le nom complet de la meilleure IA
     */
    public function getBestAiNameAttribute(): string
    {
        return match($this->meilleure_ia) {
            'gpt4' => 'GPT-4 Omni',
            'deepseek' => 'DeepSeek R1',
            'qwen' => 'Qwen 2.5 72B',
            default => $this->meilleure_ia ?? 'Non déterminé'
        };
    }

    /**
     * Vérifie si une IA spécifique est la meilleure
     */
    public function isBestAI(?string $aiKey): bool
    {
        if (is_null($aiKey) || empty($aiKey)) {
            return false;
        }
        return $this->meilleure_ia === $aiKey;
    }





    /**
     * Obtient les critères d'évaluation pour une IA selon le type d'évaluation
     */
    public function getCriteriaScores(string $aiKey): array
    {
        $evaluation = $this->getEvaluationDetails($aiKey);

        if (!is_array($evaluation)) {
            if ($this->isTranslationEvaluation()) {
                return [
                    'fidelite' => 0,
                    'qualite_linguistique' => 0,
                    'style' => 0,
                    'precision_contextuelle' => 0,
                    'hallucination' => 0,
                ];
            } elseif ($this->isMathematicalEvaluation()) {
                return [
                    'coherence_reference' => 0,
                    'justesse_math' => 0,
                    'clarte_explication' => 0,
                    'notation_rigueur' => 0,
                    'pertinence_raisonnement' => 0,
                    'hallucination' => 0,
                ];
            } else {
                return [
                    'correctitude' => 0,
                    'qualite_code' => 0,
                    'modularite' => 0,
                    'pertinence' => 0,
                    'explication' => 0,
                ];
            }
        }

        if ($this->isTranslationEvaluation()) {
            return [
                'fidelite' => (int)($evaluation['fidelite'] ?? 0),
                'qualite_linguistique' => (int)($evaluation['qualite_linguistique'] ?? 0),
                'style' => (int)($evaluation['style'] ?? 0),
                'precision_contextuelle' => (int)($evaluation['precision_contextuelle'] ?? 0),
                'hallucination' => (int)($evaluation['hallucination'] ?? 0),
            ];
        } elseif ($this->isMathematicalEvaluation()) {
            return [
                'coherence_reference' => (int)($evaluation['coherence_reference'] ?? 0),
                'justesse_math' => (int)($evaluation['justesse_math'] ?? 0),
                'clarte_explication' => (int)($evaluation['clarte_explication'] ?? 0),
                'notation_rigueur' => (int)($evaluation['notation_rigueur'] ?? 0),
                'pertinence_raisonnement' => (int)($evaluation['pertinence_raisonnement'] ?? 0),
                'hallucination' => (int)($evaluation['hallucination'] ?? 0),
            ];
        } else {
            return [
                'correctitude' => (int)($evaluation['correctitude'] ?? 0),
                'qualite_code' => (int)($evaluation['qualite_code'] ?? 0),
                'modularite' => (int)($evaluation['modularite'] ?? 0),
                'pertinence' => (int)($evaluation['pertinence'] ?? 0),
                'explication' => (int)($evaluation['explication'] ?? 0),
            ];
        }
    }

    /**
     * Obtient le commentaire pour une IA spécifique
     */
    public function getCommentForAI(string $aiKey): ?string
    {
        $evaluation = $this->getEvaluationDetails($aiKey);
        return is_array($evaluation) ? ($evaluation['commentaire'] ?? null) : null;
    }

    /**
     * Obtient le statut d'hallucination pour une IA (mathématiques seulement)
     */
    public function getHallucinationStatusForAI(string $aiKey): ?string
    {
        if (!$this->isMathematicalEvaluation()) {
            return null;
        }

        $evaluation = $this->getEvaluationDetails($aiKey);
        return is_array($evaluation) ? ($evaluation['hallucination'] ?? null) : null;
    }

    /**
     * Obtient le type d'évaluation formaté pour l'affichage
     */
    public function getEvaluationTypeDisplayAttribute(): string
    {
        return match($this->evaluation_type) {
            'mathematics' => 'Évaluation Mathématique',
            'translation' => 'Évaluation de Traduction',
            'chemistry' => 'Évaluation de Chimie',
            'programming' => 'Évaluation de Programmation',
            default => 'Évaluation Générale'
        };
    }

    /**
     * Obtient l'icône associée au type d'évaluation
     */
    public function getEvaluationTypeIconAttribute(): string
    {
        return match($this->evaluation_type) {
            'mathematics' => '🧮',
            'translation' => '🌐',
            'chemistry' => '🧪',
            'programming' => '💻',
            default => '📝'
        };
    }


    public function scopeChemistry($query)
    {
        return $query->where('evaluation_type', 'chemistry');
    }

    /**
     * Obtient les statistiques Wolfram Alpha
     */
    public function getWolframStatsAttribute(): array
    {
        return [
            'has_reference' => $this->hasWolframReference(),
            'status' => $this->wolfram_status,
            'response_time' => $this->wolfram_response_time,
            'reference_length' => $this->wolfram_reference ? strlen($this->wolfram_reference) : 0,
        ];
    }

    /**
     * Scope pour les évaluations complètes
     */
    public function scopeComplete($query)
    {
        return $query->whereNotNull('note_gpt4')
            ->whereNotNull('note_deepseek')
            ->whereNotNull('note_qwen')
            ->whereNotNull('meilleure_ia');
    }

    /**
     * Scope pour les évaluations mathématiques
     */
    public function scopeMathematics($query)
    {
        return $query->where('evaluation_type', 'mathematics');
    }

    /**
     * Scope pour les évaluations de programmation
     */
    public function scopeProgramming($query)
    {
        return $query->where('evaluation_type', 'programming');
    }

    /**
     * Scope pour les évaluations avec référence Wolfram
     */
    public function scopeWithWolframReference($query)
    {
        return $query->whereNotNull('wolfram_reference')
            ->where('wolfram_reference', '!=', '');
    }

    /**
     * Scope pour les évaluations d'aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope pour les évaluations d'une période donnée
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope pour les évaluations par meilleure IA
     */
    public function scopeByBestAI($query, string $aiKey)
    {
        return $query->where('meilleure_ia', $aiKey);
    }

    /**
     * Scope pour les évaluations avec une note minimum
     */
    public function scopeWithMinScore($query, int $minScore, string $aiKey = null)
    {
        if ($aiKey) {
            $column = "note_{$aiKey}";
            return $query->where($column, '>=', $minScore);
        }

        return $query->where(function($q) use ($minScore) {
            $q->where('note_gpt4', '>=', $minScore)
                ->orWhere('note_deepseek', '>=', $minScore)
                ->orWhere('note_qwen', '>=', $minScore);
        });
    }

    /**
     * Obtient les statistiques générales pour toutes les évaluations
     */
    public static function getGlobalStats(): array
    {
        $evaluations = self::complete()->get();

        if ($evaluations->isEmpty()) {
            return [
                'total' => 0,
                'programming' => 0,
                'mathematics' => 0,
                'average_scores' => [],
                'best_ai_frequency' => [],
                'overall_average' => 0,
                'wolfram_usage' => 0,
            ];
        }

        $mathEvaluations = $evaluations->where('evaluation_type', 'mathematics');
        $progEvaluations = $evaluations->where('evaluation_type', 'programming');

        return [
            'total' => $evaluations->count(),
            'programming' => $progEvaluations->count(),
            'mathematics' => $mathEvaluations->count(),
            'average_scores' => [
                'gpt4' => round($evaluations->avg('note_gpt4'), 2),
                'deepseek' => round($evaluations->avg('note_deepseek'), 2),
                'qwen' => round($evaluations->avg('note_qwen'), 2),
            ],
            'best_ai_frequency' => $evaluations->groupBy('meilleure_ia')
                ->map->count()
                ->toArray(),
            'overall_average' => round($evaluations->avg(function($eval) {
                return ($eval->note_gpt4 + $eval->note_deepseek + $eval->note_qwen) / 3;
            }), 2),
            'wolfram_usage' => $mathEvaluations->filter(function($eval) {
                return $eval->hasWolframReference();
            })->count(),
        ];
    }

    /**
     * Obtient les statistiques par type d'évaluation
     */
    public static function getStatsByType(): array
    {
        return [
            'translation' => [
                'total' => self::translation()->count(),
                'complete' => self::translation()->complete()->count(),
                'with_deepl' => self::translation()->withDeepLReference()->count(),
                'average_score' => round(self::translation()->complete()->get()->avg(function($eval) {
                    return ($eval->note_gpt4 + $eval->note_deepseek + $eval->note_qwen) / 3;
                }), 2),
            ],
            'mathematics' => [
                'total' => self::mathematics()->count(),
                'complete' => self::mathematics()->complete()->count(),
                'with_wolfram' => self::mathematics()->withWolframReference()->count(),
                'average_score' => round(self::mathematics()->complete()->get()->avg(function($eval) {
                    return ($eval->note_gpt4 + $eval->note_deepseek + $eval->note_qwen) / 3;
                }), 2),
            ],
            'chemistry' => [
                'total' => self::chemistry()->count(),
                'complete' => self::chemistry()->complete()->count(),
                'average_score' => round(self::chemistry()->complete()->get()->avg(function($eval) {
                    return ($eval->note_gpt4 + $eval->note_deepseek + $eval->note_qwen) / 3;
                }), 2),
            ],
            'programming' => [
                'total' => self::programming()->count(),
                'complete' => self::programming()->complete()->count(),
                'average_score' => round(self::programming()->complete()->get()->avg(function($eval) {
                    return ($eval->note_gpt4 + $eval->note_deepseek + $eval->note_qwen) / 3;
                }), 2),
            ],
        ];
    }

    public function scopeTranslation($query)
    {
        return $query->where('evaluation_type', 'translation');
    }
    public function scopeWithDeepLReference($query)
    {
        return $query->whereNotNull('deepl_reference')
            ->where('deepl_reference', '!=', '');
    }

}
