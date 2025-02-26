<?php

namespace MeShaon\RequestAnalytics\Http\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MeShaon\RequestAnalytics\Http\DTO\RequestDataDTO;
use MeShaon\RequestAnalytics\Services\RequestAnalyticsService;

class ProcessData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public RequestDataDTO $requestDataDTO) {}

    public function handle(RequestAnalyticsService $requestAnalyticsService): void
    {
        $requestAnalyticsService->store($this->requestDataDTO);
    }
}
