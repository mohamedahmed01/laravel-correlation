Laravel Correlation ID Middleware
=================================

A package to manage correlation IDs for request tracing in Laravel applications,
A correlation ID is a randomly generated identifier for every request entering a distributed system. Developers use the correlation identifier to trace the request as it makes its way through the system, identify any cyber security threats, and prevent them.
The correlation ID basically serves as a thread that connects the various parts of a request as it moves through the system. This greatly simplifies distributed system debugging and troubleshooting by allowing developers to track a request’s progress and readily pinpoint the service or component where an issue occurred.


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