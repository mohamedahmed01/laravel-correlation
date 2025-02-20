<?php

if (!function_exists('correlation_id')) {
    function correlation_id()
    {
        return app('correlation.id');
    }
}
