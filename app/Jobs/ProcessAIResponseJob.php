<?php

// app/Jobs/ProcessAIResponseJob.php
namespace App\Jobs;

use App\Models\Question;
use App\Services\OpenRouterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\IaResponse;

class ProcessAIResponseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $question;
    protected $prompt;

    public function __construct(Question $question, string $prompt)
    {
        $this->question = $question;
        $this->prompt = $prompt;
    }

    public function handle(OpenRouterService $openRouter)
    {
        try {
            // Marquer le début du traitement
            cache()->put("question_{$this->question->id}_status", 'processing', 300);

            $models = [
                'openai/gpt-4o',
                'deepseek/deepseek-r1',
                'qwen/qwen-2.5-72b-instruct',
            ];

            $responses = $openRouter->queryMultipleModelsAsync($models, $this->prompt);

            // Sauvegarder les réponses
            $this->saveResponses($responses);

            // Marquer comme terminé
            cache()->put("question_{$this->question->id}_status", 'completed', 300);
            cache()->put("question_{$this->question->id}_responses", $responses, 300);

            // Déclencher l'évaluation si c'est de la programmation
            $this->triggerEvaluationIfProgramming();

        } catch (\Exception $e) {
            \Log::error('Erreur dans ProcessAIResponseJob', [
                'question_id' => $this->question->id,
                'error' => $e->getMessage()
            ]);

            cache()->put("question_{$this->question->id}_status", 'error', 300);
            cache()->put("question_{$this->question->id}_error", $e->getMessage(), 300);
        }
    }

    protected function saveResponses(array $apiResponses): void
    {
        foreach ($apiResponses as $model => $response) {
            if ($response['status'] === 'success') {
                $content = $response['response']['choices'][0]['message']['content'] ?? 'No response';
                $tokenUsage = $response['response']['usage']['total_tokens'] ?? null;

                IaResponse::create([
                    'question_id' => $this->question->id,
                    'model_name' => $model,
                    'response' => $content,
                    'token_usage' => $tokenUsage,
                    'response_time' => $response['response_time'] ?? null,
                ]);

                // Mettre à jour le cache avec le statut par modèle
                cache()->put("question_{$this->question->id}_model_{$model}", 'completed', 300);
            } else {
                IaResponse::create([
                    'question_id' => $this->question->id,
                    'model_name' => $model,
                    'response' => 'Erreur: ' . ($response['response'] ?? 'Unknown error'),
                    'token_usage' => null,
                    'response_time' => $response['response_time'] ?? null,
                ]);

                cache()->put("question_{$this->question->id}_model_{$model}", 'error', 300);
            }
        }
    }

    protected function triggerEvaluationIfProgramming(): void
    {
        $this->question->load(['domain']);

        if ($this->isProgrammingDomain($this->question->domain)) {
            // Lancer l'évaluation après un petit délai
            dispatch(new ProcessEvaluationJob($this->question))->delay(now()->addSeconds(5));
        }
    }

    protected function isProgrammingDomain($domain): bool
    {
        if (!$domain) return false;

        $programmingKeywords = ['programmation', 'code', 'développement', 'programming', 'coding'];

        return \Str::contains(\Str::lower($domain->name), $programmingKeywords) ||
            \Str::contains(\Str::lower($domain->slug ?? ''), $programmingKeywords);
    }
}
