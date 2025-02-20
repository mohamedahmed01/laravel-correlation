<?php

namespace Mohamedahmed01\LaravelCorrelation\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CorrelationListCommand extends Command
{
    protected $signature = 'correlation:list';
    protected $description = 'List recent correlation IDs';

    public function handle()
    {
        $keys = Cache::get('correlation:keys', []);

        if (empty($keys)) {
            $this->warn('No correlation IDs found.');
            return;
        }

        foreach ($keys as $key) {
            $time = Cache::get("correlation:$key", 'Unknown time');
            $this->info("Correlation ID: $key - Time: $time");
        }
    }
}

