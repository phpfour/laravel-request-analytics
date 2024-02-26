<?php

namespace MeShaon\RequestAnalytics\Http\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use MeShaon\RequestAnalytics\Http\Middleware\RequestData;

class ProcessData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $requestData)
    {
      
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
      
    }
}
