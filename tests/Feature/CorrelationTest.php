<?php

use Mohamedahmed01\LaravelCorrelation\Http\Middleware\CorrelationMiddleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

test('generates correlation id when missing', function () {
    Route::get('/test', fn () => response())->middleware(CorrelationMiddleware::class);
    
    $response = $this->get('/test');
    
    $headerName = config('correlation.header');
    $response->assertHeader($headerName);
    expect($response->headers->get($headerName))->toBeUuid();
});

test('uses existing correlation id', function () {
    Route::get('/test', fn () => response())->middleware(CorrelationMiddleware::class);
    
    $id = Str::uuid();
    $response = $this->get('/test', [config('correlation.header') => $id]);
    
    $response->assertHeader(config('correlation.header'), $id);
});

test('correlation id appears in logs', function () {
    Route::get('/test', function () {
        \Log::info('Test log');
        return response();
    })->middleware(CorrelationMiddleware::class);
    
    \Log::shouldReceive('info')
        ->once()
        ->withArgs(fn ($message, $context) => array_key_exists('correlation_id', $context));
    
    $this->get('/test');
});

test('helper function returns correlation id', function () {
    Route::get('/test', function () {
        return response()->json(['id' => correlation_id()]);
    })->middleware(CorrelationMiddleware::class);
    
    $response = $this->get('/test');
    $headerName = config('correlation.header');
    
    expect($response->json('id'))->toBe($response->headers->get($headerName));
});