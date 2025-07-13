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
        Schema::table('evaluations', function (Blueprint $table) {
            // Type d'évaluation (programming ou mathematics)
            $table->string('evaluation_type')->default('programming')->after('question_id');

            // Référence Wolfram Alpha pour les questions mathématiques
            $table->text('wolfram_reference')->nullable()->after('commentaire_global');

            // Temps de réponse de Wolfram Alpha
            $table->decimal('wolfram_response_time', 8, 3)->nullable()->after('wolfram_reference');

            // Statut de la référence Wolfram
            $table->string('wolfram_status')->nullable()->after('wolfram_response_time');

            // Index pour optimiser les requêtes
            $table->index('evaluation_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropIndex(['evaluation_type']);
            $table->dropColumn([
                'evaluation_type',
                'wolfram_reference',
                'wolfram_response_time',
                'wolfram_status'
            ]);
        });
    }
};
