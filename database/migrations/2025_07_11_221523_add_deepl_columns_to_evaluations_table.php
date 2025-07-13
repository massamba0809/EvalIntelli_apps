<?php
// Si les colonnes manquent, créez cette migration :

// Commande pour créer la migration :
// php artisan make:migration add_deepl_columns_to_evaluations_table

// Fichier: database/migrations/XXXX_XX_XX_add_deepl_columns_to_evaluations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('evaluations', function (Blueprint $table) {
            // Colonnes pour DeepL (traduction)
            $table->text('deepl_reference')->nullable()->after('wolfram_status');
            $table->decimal('deepl_response_time', 8, 3)->nullable()->after('deepl_reference');
            $table->string('deepl_status', 50)->nullable()->after('deepl_response_time');
            $table->json('translation_data')->nullable()->after('deepl_status');
        });
    }

    public function down()
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn([
                'deepl_reference',
                'deepl_response_time',
                'deepl_status',
                'translation_data'
            ]);
        });
    }
};
