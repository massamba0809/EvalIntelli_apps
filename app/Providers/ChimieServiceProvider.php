<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ChimieEvaluationService;
use App\Services\OpenRouterService;
use App\Services\WolframAlphaService;
use App\Services\DeepLService;

class ChimieServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ChimieEvaluationService::class, function ($app) {
            return new ChimieEvaluationService(
                $app->make(OpenRouterService::class),
                $app->make(WolframAlphaService::class),
                $app->make(DeepLService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
