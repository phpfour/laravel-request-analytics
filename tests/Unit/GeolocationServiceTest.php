<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use MeShaon\RequestAnalytics\Services\GeolocationService;

beforeEach(function (): void {
    Cache::flush();
});

it('returns default location for local IPs', function (): void {
    $service = new GeolocationService;

    $result = $service->lookup('127.0.0.1');

    expect($result)->toBe([
        'country' => '',
        'country_code' => '',
        'region' => '',
        'city' => '',
        'latitude' => null,
        'longitude' => null,
        'timezone' => '',
        'isp' => '',
    ]);
});

it('detects various local IP ranges', function (): void {
    $service = new GeolocationService;

    $localIps = [
        '127.0.0.1',
        '::1',
        '192.168.1.1',
        '10.0.0.1',
        '172.16.0.1',
    ];

    foreach ($localIps as $ip) {
        $result = $service->lookup($ip);
        expect($result['country'])->toBe('');
    }
});

it('handles MaxMind web service configuration validation', function (): void {
    Config::set('request-analytics.geolocation.provider', 'maxmind');
    Config::set('request-analytics.geolocation.maxmind.type', 'webservice');
    Config::set('request-analytics.geolocation.maxmind.user_id', null);
    Config::set('request-analytics.geolocation.maxmind.license_key', null);

    Log::shouldReceive('warning')
        ->once()
        ->with('MaxMind web service credentials not configured');

    $service = new GeolocationService;
    $result = $service->lookup('8.8.8.8');

    expect($result)->toBe([
        'country' => '',
        'country_code' => '',
        'region' => '',
        'city' => '',
        'latitude' => null,
        'longitude' => null,
        'timezone' => '',
        'isp' => '',
    ]);
});

it('handles MaxMind database when file does not exist', function (): void {
    Config::set('request-analytics.geolocation.provider', 'maxmind');
    Config::set('request-analytics.geolocation.maxmind.type', 'database');
    Config::set('request-analytics.geolocation.maxmind.database_path', '/nonexistent/path.mmdb');

    Log::shouldReceive('warning')
        ->once()
        ->with('MaxMind database file not found', ['path' => '/nonexistent/path.mmdb']);

    $service = new GeolocationService;
    $result = $service->lookup('8.8.8.8');

    expect($result)->toBe([
        'country' => '',
        'country_code' => '',
        'region' => '',
        'city' => '',
        'latitude' => null,
        'longitude' => null,
        'timezone' => '',
        'isp' => '',
    ]);
});

it('handles MaxMind database when GeoIP2 library is not installed', function (): void {
    Config::set('request-analytics.geolocation.provider', 'maxmind');
    Config::set('request-analytics.geolocation.maxmind.type', 'database');

    // Create a temporary file to simulate database existence
    $tempFile = tempnam(sys_get_temp_dir(), 'test_geo');
    Config::set('request-analytics.geolocation.maxmind.database_path', $tempFile);

    Log::shouldReceive('warning')
        ->once()
        ->with('GeoIP2 library not installed. Please run: composer require geoip2/geoip2');

    $service = new GeolocationService;
    $result = $service->lookup('8.8.8.8');

    expect($result)->toBe([
        'country' => '',
        'country_code' => '',
        'region' => '',
        'city' => '',
        'latitude' => null,
        'longitude' => null,
        'timezone' => '',
        'isp' => '',
    ]);

    unlink($tempFile);
});

it('uses correct MaxMind configuration types', function (): void {
    Config::set('request-analytics.geolocation.provider', 'maxmind');

    // Test webservice type
    Config::set('request-analytics.geolocation.maxmind.type', 'webservice');
    Config::set('request-analytics.geolocation.maxmind.user_id', null);

    Log::shouldReceive('warning')
        ->once()
        ->with('MaxMind web service credentials not configured');

    $service = new GeolocationService;
    $result = $service->lookup('8.8.8.8');
    expect($result['country'])->toBe('');
});

it('handles MaxMind database type configuration', function (): void {
    Config::set('request-analytics.geolocation.provider', 'maxmind');
    Config::set('request-analytics.geolocation.maxmind.type', 'database');
    Config::set('request-analytics.geolocation.maxmind.database_path', '/nonexistent/path.mmdb');

    Log::shouldReceive('warning')
        ->once()
        ->with('MaxMind database file not found', ['path' => '/nonexistent/path.mmdb']);

    $service = new GeolocationService;
    $result = $service->lookup('8.8.8.8');
    expect($result['country'])->toBe('');
});

it('handles unknown MaxMind type gracefully', function (): void {
    Config::set('request-analytics.geolocation.provider', 'maxmind');
    Config::set('request-analytics.geolocation.maxmind.type', 'unknown_type');

    $service = new GeolocationService;
    $result = $service->lookup('8.8.8.8');

    expect($result)->toBe([
        'country' => '',
        'country_code' => '',
        'region' => '',
        'city' => '',
        'latitude' => null,
        'longitude' => null,
        'timezone' => '',
        'isp' => '',
    ]);
});
