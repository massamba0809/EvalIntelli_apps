<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Evaluation;

class FixEvaluationDataCommand extends Command
{
    protected $signature = 'fix:evaluation-data {--dry-run : Voir les changements sans les appliquer}';
    protected $description = 'Répare les données d\'évaluation mal formatées';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('🔧 Réparation des données d\'évaluation...');

        $evaluations = Evaluation::all();
        $fixed = 0;
        $errors = 0;

        foreach ($evaluations as $evaluation) {
            $changes = [];

            // Vérifier et réparer evaluation_gpt4
            if ($this->needsFixing($evaluation->evaluation_gpt4)) {
                $fixed_data = $this->fixJsonData($evaluation->evaluation_gpt4);
                if ($fixed_data !== null) {
                    $changes['evaluation_gpt4'] = $fixed_data;
                }
            }

            // Vérifier et réparer evaluation_deepseek
            if ($this->needsFixing($evaluation->evaluation_deepseek)) {
                $fixed_data = $this->fixJsonData($evaluation->evaluation_deepseek);
                if ($fixed_data !== null) {
                    $changes['evaluation_deepseek'] = $fixed_data;
                }
            }

            // Vérifier et réparer evaluation_qwen
            if ($this->needsFixing($evaluation->evaluation_qwen)) {
                $fixed_data = $this->fixJsonData($evaluation->evaluation_qwen);
                if ($fixed_data !== null) {
                    $changes['evaluation_qwen'] = $fixed_data;
                }
            }

            if (!empty($changes)) {
                $this->warn("Évaluation ID {$evaluation->id} nécessite une réparation:");
                foreach ($changes as $field => $newValue) {
                    $this->line("  - {$field}: " . (is_array($newValue) ? 'JSON valide' : 'null'));
                }

                if (!$dryRun) {
                    try {
                        $evaluation->update($changes);
                        $fixed++;
                        $this->info("  ✅ Réparée!");
                    } catch (\Exception $e) {
                        $errors++;
                        $this->error("  ❌ Erreur: " . $e->getMessage());
                    }
                } else {
                    $this->line("  🔍 Mode dry-run: changements non appliqués");
                }
            }
        }

        $this->newLine();
        $this->info("📊 Résumé:");
        $this->info("- Total d'évaluations: " . $evaluations->count());
        $this->info("- Évaluations réparées: {$fixed}");
        if ($errors > 0) {
            $this->error("- Erreurs: {$errors}");
        }

        if ($dryRun && $fixed > 0) {
            $this->warn("Pour appliquer les changements, relancez sans --dry-run");
        }
    }

    private function needsFixing($data): bool
    {
        // Si c'est une chaîne, ça doit être réparé
        if (is_string($data)) {
            return true;
        }

        // Si c'est null ou déjà un array, pas besoin de réparation
        return false;
    }

    private function fixJsonData($data): ?array
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
