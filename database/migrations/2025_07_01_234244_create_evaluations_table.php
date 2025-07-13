<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');

            // Évaluations détaillées pour chaque IA (stockage JSON)
            $table->json('evaluation_gpt4')->nullable();
            $table->json('evaluation_deepseek')->nullable();
            $table->json('evaluation_qwen')->nullable();

            // Notes sur 10 pour chaque IA
            $table->integer('note_gpt4')->nullable()->comment('Note sur 10');
            $table->integer('note_deepseek')->nullable()->comment('Note sur 10');
            $table->integer('note_qwen')->nullable()->comment('Note sur 10');

            // IA jugée la meilleure
            $table->string('meilleure_ia')->nullable();

            // Commentaire global de Claude
            $table->text('commentaire_global')->nullable();

            // Métadonnées sur l'évaluation
            $table->integer('token_usage_evaluation')->nullable()->comment('Tokens utilisés pour l\'évaluation');
            $table->decimal('response_time_evaluation', 8, 3)->nullable()->comment('Temps de réponse en secondes');

            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index('question_id');
            $table->index('meilleure_ia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
