<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
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
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
