<?php

namespace MeShaon\RequestAnalytics\Traits;

use Illuminate\Http\Request;
use MeShaon\RequestAnalytics\Http\DTO\RequestDataDTO;
use MeShaon\RequestAnalytics\Services\BotDetectionService;
use MeShaon\RequestAnalytics\Services\GeolocationService;
use MeShaon\RequestAnalytics\Services\VisitorTrackingService;
use Symfony\Component\HttpFoundation\Response;
use UAParser\Parser;

trait CaptureRequest
{
    public function capture(Request $request, Response $response, string $requestCategory): ?RequestDataDTO
    {
        if ($this->shouldIgnore($request->path())) {
            return null;
        }

        // Respect Do Not Track header if enabled
        if (config('request-analytics.privacy.respect_dnt') && $request->header('DNT') === '1') {
            return null;
        }

        // Skip bot traffic unless explicitly enabled
        if ($this->isBot($request) && ! config('request-analytics.capture.bots', false)) {
            return null;
        }

        return $this->prepareRequestData($request, $response, $requestCategory);
    }

    protected function prepareRequestData(Request $request, Response $response, string $requestCategory): RequestDataDTO
    {
        $path = $request->path();
        $content = $response->getContent();
        $browserInfo = $this->parseUserAgent($request->header('User-Agent'));
        $ipAddress = $this->getIpAddress($request);
        $referrer = $request->header('referer', '');

        // Get country from geolocation or CloudFlare header
        $country = '';
        $city = '';
        if (config('request-analytics.geolocation.enabled')) {
            $geo = new GeolocationService;
            $location = $geo->lookup($ipAddress);
            $country = $location['country'] ?: $request->header('CF-IPCountry', '');
            $city = $location['city'] ?? '';
        } else {
            $country = $request->header('CF-IPCountry', '');
        }

        $language = $request->header('Accept-Language', '');
        $queryParams = json_encode($request->query());
        $httpMethod = $request->method();
        $responseTime = defined('LARAVEL_START') ? microtime(true) - LARAVEL_START : 0;

        // Get visitor and session IDs
        $visitorTracking = new VisitorTrackingService;
        $visitorId = $visitorTracking->getVisitorId($request);
        $sessionId = $visitorTracking->getSessionId($request);

        return new RequestDataDTO(
            $path,
            $content,
            $browserInfo,
            $ipAddress,
            $referrer,
            $country,
            $city,
            $language,
            $queryParams,
            $httpMethod,
            $responseTime,
            $requestCategory,
            $sessionId,
            $visitorId
        );
    }

    protected function getIpAddress(Request $request): string
    {
        $ip = $request->ip() ?? $request->server('REMOTE_ADDR');

        // Anonymize IP if privacy setting is enabled
        if (config('request-analytics.privacy.anonymize_ip')) {
            return $this->anonymizeIp($ip);
        }

        return $ip;
    }

    protected function anonymizeIp(string $ip): string
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // For IPv4, zero out the last octet
            $parts = explode('.', $ip);
            $parts[3] = '0';

            return implode('.', $parts);
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // For IPv6, zero out the last 80 bits
            $parts = explode(':', $ip);
            $counter = count($parts);
            for ($i = 3; $i < $counter; $i++) {
                $parts[$i] = '0';
            }

            return implode(':', $parts);
        }

        return $ip;
    }

    protected function parseUserAgent($userAgent): array
    {
        $parser = Parser::create();
        $result = $parser->parse($userAgent);

        return [
            'operating_system' => $result->os->family,
            'browser' => $result->ua->family,
            'device' => $result->device->family,
        ];
    }

    protected function shouldIgnore(string $path): bool
    {
        $ignorePaths = array_merge(config('request-analytics.ignore-paths', []), [config('request-analytics.route.pathname')]);

        // Normalize the path by removing leading slash if present
        $normalizedPath = ltrim($path, '/');

        foreach ($ignorePaths as $ignorePath) {
            // Skip empty ignore paths
            if (empty($ignorePath)) {
                continue;
            }

            // Normalize the ignore path by removing leading slash if present
            $normalizedIgnorePath = ltrim((string) $ignorePath, '/');

            // Handle exact matches
            if ($normalizedPath === $normalizedIgnorePath) {
                return true;
            }

            // Handle wildcard patterns
            if (str_contains($normalizedIgnorePath, '*')) {
                // Convert wildcard pattern to regex pattern
                // First escape special regex chars, then replace \* with .*
                $pattern = preg_quote($normalizedIgnorePath, '#');
                $pattern = str_replace('\\*', '.*', $pattern);
                if (preg_match('#^'.$pattern.'$#', $normalizedPath)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function isBot(Request $request): bool
    {
        $botDetector = new BotDetectionService;

        return $botDetector->isBot(
            $request->header('User-Agent'),
            $request->ip()
        );
    }
}
