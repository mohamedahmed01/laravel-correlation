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

        // Add to application instance
        app()->instance('correlation.id', $correlationId);

        // Add to log context using processor
        $this->addLogProcessor($correlationId);

        $response = $next($request);
        $response->headers->set($headerName, $correlationId);

        return $response;
    }

    protected function addLogProcessor(string $correlationId): void
    {
        $logger = app('log');
        $logger->pushProcessor(function ($record) use ($correlationId) {
            $record['context']['correlation_id'] = $correlationId;
            return $record;
        });
    }
}