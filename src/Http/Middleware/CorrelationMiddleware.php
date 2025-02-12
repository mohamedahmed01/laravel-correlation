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
        if (method_exists(\Log::class, 'shareContext')) {
            \Log::shareContext(['correlation_id' => $correlationId]);
        }else{
            config(['logging.context.correlation_id' => $correlationId]);
        }
        app()->instance('correlation.id', $correlationId);
        
        $response = $next($request);

        $response->headers->set($headerName, $correlationId);

        return $response;
    }
}