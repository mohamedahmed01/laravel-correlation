<?php

namespace Mohamedahmed01\laravelCorrelation\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Mohamedahmed01\laravelCorrelation\CorrelationServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [CorrelationServiceProvider::class];
    }
}