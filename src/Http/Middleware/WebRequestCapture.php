<?php

namespace MeShaon\RequestAnalytics\Http\Middleware;

use MeShaon\RequestAnalytics\Http\DTO\RequestDataDTO;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use MeShaon\RequestAnalytics\Http\Jobs\ProcessData;
use MeShaon\RequestAnalytics\Traits\CaptureRequest;

class WebRequestCapture
{
    use CaptureRequest;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        try {
            if (($requestData = $this->capture($request, $response, 'web')) instanceof RequestDataDTO) {
                if (config('request-analytics.queue.enabled', true)) {
                    ProcessData::dispatch($requestData);
                } else {
                    ProcessData::dispatchSync($requestData);
                }
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
