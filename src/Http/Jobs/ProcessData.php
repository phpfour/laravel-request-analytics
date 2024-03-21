<?php

namespace MeShaon\RequestAnalytics\Http\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MeShaon\RequestAnalytics\Http\DTO\RequestDataDto;

class ProcessData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public RequestDataDto $requestDataDTO)
    {
    }

    public function handle(): void
    {
        try {
            $requestData = [
                'url' => $this->requestDataDTO->url,
                'page_title' => $this->extractPageTitle($this->requestDataDTO->content),
                'ip_address' => $this->requestDataDTO->ipAddress,
                'operating_system' => $this->requestDataDTO->browserInfo['operating_system'],
                'browser' => $this->requestDataDTO->browserInfo['browser'],
                'device' => $this->requestDataDTO->browserInfo['device'],
                'screen' => '',
                'referrer' => $this->requestDataDTO->referrer,
                'country' => $this->requestDataDTO->country,
                'city' => '',
                'language' => $this->requestDataDTO->language,
                'query_params' => $this->requestDataDTO->queryParams,
                'session_id' => session()->getId(),
                'user_id' => Auth::id(),
                'http_method' => $this->requestDataDTO->httpMethod,
                'request_type' => '',
                'response_time' => $this->requestDataDTO->responseTime,
            ];
        } catch (\Throwable $e) {
        }
    }

    private function extractPageTitle($content)
    {
        $matches = [];
        preg_match('/<title>(.*?)<\/title>/i', $content, $matches);
        return isset($matches[1]) ? $matches[1] : '';
    }
}
