<?php

namespace Mohamedahmed01\LaravelCorrelation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CorrelationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $headerName = config('correlation.header', 'X-Correlation-ID');
        $alternateHeaders = config('correlation.alternate_headers', []);

        $correlationId = $this->getCorrelationId($request, $headerName, $alternateHeaders);

        app()->instance('correlation.id', $correlationId);

        if (config('correlation.log')) {
            $this->addLogContext($correlationId);
        }

        if (config('correlation.storage') === 'cache') {
            Cache::put('correlation:' . $correlationId, now()->toDateTimeString(), 3600);
        }

        if (config('correlation.queue')) {
            Queue::before(fn($event) => $event->job->setCorrelationId($correlationId));
        }

        $response = $next($request);
        $response->headers->set($headerName, $correlationId);

        return $response;
    }

    protected function getCorrelationId(Request $request, string $headerName, array $alternateHeaders): string
    {
        // Check for existing correlation ID
        foreach ([$headerName, ...$alternateHeaders] as $header) {
            if ($id = $request->header($header)) {
                return $id;
            }
        }

        // Generate a new correlation ID
        return match (config('correlation.generator', 'uuid')) {
            'timestamp' => now()->timestamp . Str::random(5),
            'hash' => hash('sha256', Str::uuid()->toString()),
            default => Str::uuid()->toString(),
        };
    }

    protected function addLogContext(string $correlationId): void
    {
        if (method_exists(\Log::class, 'shareContext')) {
            \Log::shareContext(['correlation_id' => $correlationId]);
        }
        
        config(['logging.context.correlation_id' => $correlationId]);
    
        $logger = app('log')->getLogger();
        $logger->pushProcessor(function ($record) use ($correlationId) {
            if ($record instanceof \Monolog\LogRecord) {
                return $record->with(
                    context: array_merge($record->context, ['correlation_id' => $correlationId])
                );
            }
            
            $record['context']['correlation_id'] = $correlationId;
            return $record;
        });
    }
}