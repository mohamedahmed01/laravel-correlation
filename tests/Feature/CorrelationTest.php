<?php

use Mohamedahmed01\LaravelCorrelation\Http\Middleware\CorrelationMiddleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\TestHandler;

test('generates correlation id when missing', function () {
    Route::get('/test', fn () => response())->middleware(CorrelationMiddleware::class);
    
    $response = $this->get('/test');
    
    $headerName = config('correlation.header');
    $correlationId = $response->headers->get($headerName);
    
    $response->assertHeader($headerName);
    expect($correlationId)->toBeString()->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

test('uses existing correlation id', function () {
    Route::get('/test', fn () => response())->middleware(CorrelationMiddleware::class);
    
    $id = Str::uuid();
    $response = $this->get('/test', [config('correlation.header') => $id]);
    
    $response->assertHeader(config('correlation.header'), $id);
});

test('correlation id appears in logs', function () {
    $testHandler = new TestHandler();
    Log::channel('stack')->getLogger()->pushHandler($testHandler);

    Route::get('/test', function () {
        Log::info('Test log');
        return response();
    })->middleware(CorrelationMiddleware::class);

    // Send request
    $this->get('/test');

    $logs = $testHandler->getRecords();

    expect($logs)->toHaveCount(1)
        ->and($logs[0]['message'])->toBe('Test log')
        ->and($logs[0]['context'])->toHaveKey('correlation_id')
        ->and($logs[0]['context']['correlation_id'])->toBeUuid();
});

test('helper function returns correlation id', function () {
    Route::get('/test', function () {
        return response()->json(['id' => correlation_id()]);
    })->middleware(CorrelationMiddleware::class);
    
    $response = $this->get('/test');
    $headerName = config('correlation.header');
    
    expect($response->json('id'))->toBe($response->headers->get($headerName));
});