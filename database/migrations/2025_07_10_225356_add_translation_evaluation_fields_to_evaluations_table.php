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
            // Champs spécifiques aux évaluations de traduction
            $table->text('deepl_reference')->nullable()->after('wolfram_status');
            $table->decimal('deepl_response_time', 8, 3)->nullable()->after('deepl_reference');
            $table->string('deepl_status')->nullable()->after('deepl_response_time');
            $table->json('translation_data')->nullable()->after('deepl_status');

            // Index pour optimiser les requêtes sur les traductions
            $table->index(['evaluation_type', 'deepl_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropIndex(['evaluation_type', 'deepl_status']);
            $table->dropColumn([
                'deepl_reference',
                'deepl_response_time',
                'deepl_status',
                'translation_data'
            ]);
        });
    }
};
