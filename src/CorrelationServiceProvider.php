<?php

namespace Mohamedahmed01\LaravelCorrelation;

use Illuminate\Support\ServiceProvider;
use Mohamedahmed01\LaravelCorrelation\Http\Middleware\CorrelationMiddleware;

class CorrelationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/correlation.php' => config_path('correlation.php'),
        ], 'correlation-config');

        if (config('correlation.auto_register_middleware', true)) {
            $this->app['router']->pushMiddlewareToGroup('web', CorrelationMiddleware::class);
            $this->app['router']->pushMiddlewareToGroup('api', CorrelationMiddleware::class);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/correlation.php', 'correlation');
    }
}