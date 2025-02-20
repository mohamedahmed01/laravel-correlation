<?php

return [
    'header' => env('CORRELATION_HEADER', 'X-Correlation-ID'),

    // Define ID generation method: uuid, timestamp, hash
    'generator' => env('CORRELATION_GENERATOR', 'uuid'),

    // Enable logging integration
    'log' => true,

    // Enable correlation ID propagation in HTTP requests
    'propagate' => true,

    // Enable queue support (attaching correlation ID to jobs)
    'queue' => true,

    // Enable storing correlation IDs in cache or database
    'storage' => env('CORRELATION_STORAGE', 'cache'), // options: cache, database, none

    // Alternate header names for compatibility
    'alternate_headers' => ['X-Request-ID', 'Trace-ID'],

    'auto_register_middleware' => env('CORRELATION_AUTO_REGISTER', true),
];