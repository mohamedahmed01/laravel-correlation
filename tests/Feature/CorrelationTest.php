<?php

use Mohamedahmed01\LaravelCorrelation\Http\Middleware\CorrelationMiddleware;
use Mohamedahmed01\LaravelCorrelation\CorrelationServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Queue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Monolog\Handler\TestHandler;
use Tests\TestCase;

beforeEach(function () {
    Cache::flush(); // Ensure a clean cache before each test
});

/**
 * Test if the middleware generates a correlation ID when missing
 */
test('generates correlation id when missing', function () {
    Route::get('/test', fn () => response())->middleware(CorrelationMiddleware::class);

    $response = $this->get('/test');

    $headerName = config('correlation.header');
    $correlationId = $response->headers->get($headerName);

    $response->assertHeader($headerName);
    expect($correlationId)->toBeString()
        ->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

/**
 * Test if middleware uses an existing correlation ID from the request
 */
test('uses existing correlation id', function () {
    Route::get('/test', fn () => response())->middleware(CorrelationMiddleware::class);

    $id = Str::uuid();
    $response = $this->get('/test', [config('correlation.header') => $id]);

    $response->assertHeader(config('correlation.header'), $id);
});

/**
 * Test if correlation ID appears in logs
 */
test('correlation id appears in logs', function () {
    app()->forgetInstance('log');

    $testHandler = new TestHandler();
    Log::getLogger()->setHandlers([$testHandler]);

    Route::get('/test', function () {
        Log::info('Test log message');
        return response('OK');
    })->middleware(CorrelationMiddleware::class);

    $response = $this->get('/test');
    $response->assertOk();

    $logs = $testHandler->getRecords();
    $testLog = collect($logs)->first(fn ($log) => $log['message'] === 'Test log message');

    expect($testLog['context'])->toHaveKey('correlation_id')
        ->and($testLog['context']['correlation_id'])->toBe($response->headers->get(config('correlation.header')));
});

/**
 * Test if alternate headers are used for correlation ID extraction
 */
test('supports alternate correlation headers', function () {
    config(['correlation.alternate_headers' => ['X-Request-ID', 'Trace-ID']]);

    Route::get('/test', fn () => response())->middleware(CorrelationMiddleware::class);

    $id = Str::uuid();
    $response = $this->get('/test', ['X-Request-ID' => $id]);

    $response->assertHeader(config('correlation.header'), $id);
});

/**
 * Test different correlation ID generation strategies
 */
test('supports different correlation ID generation strategies', function () {
    config(['correlation.generator' => 'timestamp']);
    Route::get('/test', fn () => response())->middleware(CorrelationMiddleware::class);
    
    $response = $this->get('/test');
    expect($response->headers->get(config('correlation.header')))->toMatch('/^\d{10}[a-zA-Z0-9]{5}$/');

    config(['correlation.generator' => 'hash']);
    $response = $this->get('/test');
    expect($response->headers->get(config('correlation.header')))->toMatch('/^[a-f0-9]{64}$/');
});

/**
 * Test correlation ID caching
 */
test('stores correlation ID in cache', function () {
    config(['correlation.storage' => 'cache']);

    Route::get('/test', fn () => response())->middleware(CorrelationMiddleware::class);

    $response = $this->get('/test');
    $correlationId = $response->headers->get(config('correlation.header'));

    expect(Cache::get('correlation:' . $correlationId))->not->toBeNull();
});

/**
 * Test correlation ID propagation in queued jobs
 */
test('correlation id is passed to queued jobs', function () {
    config(['correlation.queue' => true]);

    Queue::fake();

    // Define a dedicated job class for testing
    class TestCorrelationJob implements ShouldQueue
    {
        public string $correlationId;

        public function __construct(string $id)
        {
            $this->correlationId = $id;
        }

        public function handle()
        {
            Log::info('Processing job', ['correlation_id' => $this->correlationId]);
        }
    }

    Route::get('/test', function (Request $request) {
        dispatch(new TestCorrelationJob(correlation_id()));

        return response('Job dispatched');
    })->middleware(CorrelationMiddleware::class);

    $response = $this->get('/test');
    $correlationId = $response->headers->get(config('correlation.header'));

    Queue::assertPushed(TestCorrelationJob::class, function (TestCorrelationJob $job) use ($correlationId) {
        return $job->correlationId === $correlationId;
    });
});


/**
 * Test that the correlation ID is correctly propagated in HTTP requests.
 */
test('correlation ID is propagated in HTTP requests', function () {
    config(['correlation.propagate' => true]);

    $correlationHeader = config('correlation.header');
    $correlationId = 'test-correlation-id';

    Http::fake([
        'https://google.com' => Http::response([
            'headers' => [$correlationHeader => $correlationId],
        ], 200),
    ]);

    Route::get('/test', function () {
        $response = Http::withCorrelationId()->get('https://google.com');
        return response()->json($response->json());
    })->middleware(CorrelationMiddleware::class);

    $response = $this->get('/test');
    $responseHeaders = $response->json('headers', []);

    expect($responseHeaders)->toHaveKey($correlationHeader, $correlationId);
});


/**
 * Test that the Blade directive correctly outputs the correlation ID.
 */
test('Blade directive outputs correlation ID', function () {
    // Manually bind 'correlation.id' to a test value
    app()->singleton('correlation.id', fn () => 'test-correlation-id');

    $compiled = Blade::compileString('@correlationId');

    // Manually evaluate the compiled PHP code
    ob_start();
    eval('?>' . $compiled);
    $output = ob_get_clean();

    expect($output)->toBe('test-correlation-id');
});

/**
 * Test artisan command for listing correlation IDs
 */
test('artisan command lists correlation IDs', function () {
    config(['cache.default' => 'array']); // Use an in-memory cache

    Cache::put('correlation:keys', ['test-id-123'], 3600);
    Cache::put('correlation:test-id-123', now()->toDateTimeString(), 3600);

    $this->artisan('correlation:list')
        ->expectsOutput("Correlation ID: test-id-123 - Time: " . Cache::get('correlation:test-id-123'))
        ->assertExitCode(0);
});
