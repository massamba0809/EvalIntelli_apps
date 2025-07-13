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
        // Modifier la table ia_responses pour cascade delete
        Schema::table('ia_responses', function (Blueprint $table) {
            // Supprimer la contrainte existante
            $table->dropForeign(['question_id']);

            // Ajouter la nouvelle contrainte avec cascade
            $table->foreign('question_id')
                ->references('id')
                ->on('questions')
                ->onDelete('cascade');
        });

        // Modifier la table evaluations pour cascade delete (si elle existe)
        if (Schema::hasTable('evaluations')) {
            Schema::table('evaluations', function (Blueprint $table) {
                // Supprimer la contrainte existante
                $table->dropForeign(['question_id']);

                // Ajouter la nouvelle contrainte avec cascade
                $table->foreign('question_id')
                    ->references('id')
                    ->on('questions')
                    ->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer les contraintes originales
        Schema::table('ia_responses', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
            $table->foreign('question_id')->references('id')->on('questions');
        });

        if (Schema::hasTable('evaluations')) {
            Schema::table('evaluations', function (Blueprint $table) {
                $table->dropForeign(['question_id']);
                $table->foreign('question_id')->references('id')->on('questions');
            });
        }
    }
};
