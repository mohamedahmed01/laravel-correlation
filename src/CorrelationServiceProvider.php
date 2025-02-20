<?php

namespace Mohamedahmed01\LaravelCorrelation;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Http;

use Mohamedahmed01\LaravelCorrelation\Http\Middleware\CorrelationMiddleware;
use Mohamedahmed01\LaravelCorrelation\CorrelationServiceProvider;

class CorrelationServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        Blade::directive('correlationId', function () {
            return "<?php echo correlation_id(); ?>";
        });

        $this->publishes([
            __DIR__.'/../config/correlation.php' => config_path('correlation.php'),
        ], 'correlation-config');

        if (config('correlation.auto_register_middleware', true)) {
            $this->app['router']->pushMiddlewareToGroup('web', CorrelationMiddleware::class);
            $this->app['router']->pushMiddlewareToGroup('api', CorrelationMiddleware::class);
        }

        Http::macro('withCorrelationId', function () {
            return Http::withHeaders([
                config('correlation.header') => correlation_id(),
            ]);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Mohamedahmed01\LaravelCorrelation\Console\Commands\CorrelationListCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/correlation.php', 'correlation');
    }
}