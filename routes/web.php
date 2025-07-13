<?php

use App\Http\Controllers\IaComparisonController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\SidebarApiController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {

    // === ROUTES PRINCIPALES ===
    Route::get('/dashboard', [IaComparisonController::class, 'index'])->name('dashboard');

    // Routes IA principales
    Route::prefix('ia')->group(function () {
        Route::get('/', [IaComparisonController::class, 'index'])->name('ia.index');
        Route::get('/{domain}', [QuestionController::class, 'showForm'])->name('ia.form');
        Route::post('/{domain}', [QuestionController::class, 'submit'])->name('ia.submit');

        // Route pour afficher les résultats d'une question spécifique
        Route::get('/results', [QuestionController::class, 'showResultsById'])->name('ia.results.by.id');
    });

    // === ROUTES ÉVALUATIONS (PROGRAMMATION + MATHÉMATIQUES) ===
    Route::prefix('questions')->group(function () {
        // Routes d'évaluation principales
        Route::post('/{question}/evaluate', [EvaluationController::class, 'evaluateQuestion'])
            ->name('questions.evaluate');

        Route::get('/{question}/evaluation', [EvaluationController::class, 'show'])
            ->name('questions.evaluation.show');

        Route::get('/{question}/evaluation/status', [EvaluationController::class, 'getStatus'])
            ->name('questions.evaluation.status');

        // NOUVELLE ROUTE : Résumé rapide de l'évaluation
        Route::get('/{question}/evaluation/summary', [EvaluationController::class, 'getSummary'])
            ->name('questions.evaluation.summary');

        // NOUVELLE ROUTE : Déclenchement manuel d'évaluation
        Route::post('/{question}/trigger-evaluation', [EvaluationController::class, 'triggerManualEvaluation'])
            ->name('questions.trigger.evaluation');

        Route::post('/{question}/reprocess', [EvaluationController::class, 'reprocess'])
            ->name('questions.reprocess');
    });

    // Route pour les statistiques d'évaluation
    Route::get('/evaluations/stats', [EvaluationController::class, 'getStats'])
        ->name('evaluations.stats');


    // === ROUTES API SIDEBAR ===
    Route::prefix('api')->group(function () {
        // Gestion des questions utilisateur
        Route::get('/user/questions', [SidebarApiController::class, 'getUserQuestions'])
            ->name('api.user.questions');

        Route::get('/user/questions/{question}', [SidebarApiController::class, 'getQuestionDetails'])
            ->name('api.user.question.details');

        // Recherche
        Route::get('/user/search', [SidebarApiController::class, 'searchUserQuestions'])
            ->name('api.user.search');

        // Statistiques utilisateur
        Route::get('/user/stats', [SidebarApiController::class, 'getUserStats'])
            ->name('api.user.stats');

        // Questions récentes
        Route::get('/user/recent', [SidebarApiController::class, 'getRecentQuestions'])
            ->name('api.user.recent');

        // Suppression
        Route::delete('/user/questions/{question}', [SidebarApiController::class, 'deleteQuestion'])
            ->name('api.user.question.delete');

        Route::delete('/user/questions', [SidebarApiController::class, 'deleteBulkQuestions'])
            ->name('api.user.questions.bulk.delete');

        Route::delete('/user/history/clear', [SidebarApiController::class, 'clearAllHistory'])
            ->name('api.user.history.clear');
    });
});





