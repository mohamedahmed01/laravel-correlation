Laravel Correlation ID Middleware
=================================

A package to manage correlation IDs for request tracing in Laravel applications.

Installation
------------

    composer require mohamedahmed01/laravel-correlation

Configuration
-------------

Publish the config file:

    php artisan vendor:publish --tag=correlation-config

Config options (`config/correlation.php`):

*   `header`: Header name to use (default: X-Correlation-ID)
*   `auto_register_middleware`: Automatically register middleware (default: true)

Usage
-----

The correlation ID will be:

1.  Read from incoming requests
2.  Generated if missing
3.  Added to all responses
4.  Available in logs
5.  Accessible via `correlation_id()` helper

### Manual Middleware Registration

Add to `app/Http/Kernel.php`:

    protected $middleware = [
        \Mohamedahmed01\LaravelCorrelation\Http\Middleware\CorrelationMiddleware::class,
    ];

### Accessing the Correlation ID

    $correlationId = correlation_id();

### Logging

All logs during a request will automatically include:

    ['correlation_id' => 'your-uuid']