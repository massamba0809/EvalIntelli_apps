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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Logique/Math", "Programmation", etc.
            $table->string('slug')->unique();
            $table->text('prompt_template')->nullable(); // Template de prompt spécifique au domaine
            $table->string('icon', 10)->nullable(); // Emoji pour l'icône
            $table->text('description')->nullable(); // Description du domaine
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
