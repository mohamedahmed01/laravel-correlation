<?php

namespace Mohamedahmed01\LaravelCorrelation\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Mohamedahmed01\LaravelCorrelation\CorrelationServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [CorrelationServiceProvider::class];
    }
}