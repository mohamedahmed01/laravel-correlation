<?php

namespace Mohamedahmed01\LaravelCorrelation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CorrelationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $headerName = config('correlation.header', 'X-Correlation-ID');
        $correlationId = $request->header($headerName) ?? Str::uuid()->toString();

        // Store in application container
        app()->instance('correlation.id', $correlationId);

        // Add to log context using multiple methods
        $this->addLogContext($correlationId);

        $response = $next($request);
        $response->headers->set($headerName, $correlationId);

        return $response;
    }

    protected function addLogContext(string $correlationId): void
    {
        // Method 1: For Laravel 8.65+ with shareContext
        if (method_exists(\Log::class, 'shareContext')) {
            \Log::shareContext(['correlation_id' => $correlationId]);
        }
        
        // Method 2: For older versions using config
        config(['logging.context.correlation_id' => $correlationId]);

        // Method 3: Direct processor for Monolog
        $logger = app('log')->getLogger();
        $logger->pushProcessor(function ($record) use ($correlationId) {
            $record['context']['correlation_id'] = $correlationId;
            return $record;
        });
    }
}