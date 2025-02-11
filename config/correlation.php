<?php

return [
    'header' => env('CORRELATION_HEADER', 'X-Correlation-ID'),
    'auto_register_middleware' => env('CORRELATION_AUTO_REGISTER', true),
];