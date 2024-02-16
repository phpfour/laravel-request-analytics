<?php

namespace MeShaon\RequestAnalytics\Commands;

use Illuminate\Console\Command;

class RequestAnalyticsCommand extends Command
{
    public $signature = 'laravel-request-analytics';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
