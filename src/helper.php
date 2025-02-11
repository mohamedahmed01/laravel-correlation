<?php

use Illuminate\Contracts\Foundation\Application;

if (! function_exists('correlation_id')) {
    function correlation_id(): ?string
    {
        return app()->has('correlation.id') ? app('correlation.id') : null;
    }
}