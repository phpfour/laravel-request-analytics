<?php

namespace MeShaon\RequestAnalytics\Services;

use Illuminate\Support\Facades\Auth;
use MeShaon\RequestAnalytics\Http\DTO\RequestDataDTO;
use MeShaon\RequestAnalytics\Models\RequestAnalytics;

class RequestAnalyticsService
{
    public function store(RequestDataDTO $requestDataDTO)
    {
        $requestData = [
            'url' => $requestDataDTO->url,
            'page_title' => $this->extractPageTitle($requestDataDTO->content),
            'ip_address' => $requestDataDTO->ipAddress,
            'operating_system' => $requestDataDTO->browserInfo['operating_system'],
            'browser' => $requestDataDTO->browserInfo['browser'],
            'device' => $requestDataDTO->browserInfo['device'],
            'screen' => '',
            'referrer' => $requestDataDTO->referrer,
            'country' => $requestDataDTO->country,
            'city' => '',
            'language' => $requestDataDTO->language,
            'query_params' => $requestDataDTO->queryParams,
            'session_id' => session()->getId(),
            'user_id' => Auth::id(),
            'http_method' => $requestDataDTO->httpMethod,
            'request_type' => $requestDataDTO->requestType,
            'response_time' => $requestDataDTO->responseTime,
            'visited_at' => now(),
        ];

        return RequestAnalytics::create($requestData);
    }

    private function extractPageTitle(string $content)
    {
        $matches = [];
        preg_match('/<title>(.*?)<\/title>/i', $content, $matches);

        return $matches[1] ?? '';
    }
}
