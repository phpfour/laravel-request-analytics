<?php

use MeShaon\RequestAnalytics\Exceptions\BotDetectionException;
use MeShaon\RequestAnalytics\Exceptions\GeolocationException;
use MeShaon\RequestAnalytics\Exceptions\GeolocationProviderException;
use MeShaon\RequestAnalytics\Exceptions\MaxMindConfigurationException;
use MeShaon\RequestAnalytics\Exceptions\MaxMindDependencyException;
use MeShaon\RequestAnalytics\Exceptions\RequestAnalyticsException;
use MeShaon\RequestAnalytics\Exceptions\RequestAnalyticsStorageException;

it('creates geolocation provider exception with correct data', function (): void {
    $exception = new GeolocationProviderException('ipapi', '8.8.8.8', 'API failed', 500);

    expect($exception)->toBeInstanceOf(GeolocationException::class);
    expect($exception)->toBeInstanceOf(RequestAnalyticsException::class);
    expect($exception->getProvider())->toBe('ipapi');
    expect($exception->getIpAddress())->toBe('8.8.8.8');
    expect($exception->getMessage())->toBe('API failed');
    expect($exception->getCode())->toBe(500);
});

it('creates geolocation provider exception with default message', function (): void {
    $exception = new GeolocationProviderException('maxmind', '1.2.3.4');

    expect($exception->getMessage())->toBe('Geolocation lookup failed for IP 1.2.3.4 using provider maxmind');
    expect($exception->getProvider())->toBe('maxmind');
    expect($exception->getIpAddress())->toBe('1.2.3.4');
});

it('creates maxmind configuration exception with correct data', function (): void {
    $exception = new MaxMindConfigurationException('webservice', 'Missing credentials', 400);

    expect($exception)->toBeInstanceOf(GeolocationException::class);
    expect($exception->getConfigurationType())->toBe('webservice');
    expect($exception->getMessage())->toBe('Missing credentials');
    expect($exception->getCode())->toBe(400);
});

it('creates maxmind configuration exception with default message', function (): void {
    $exception = new MaxMindConfigurationException('database');

    expect($exception->getMessage())->toBe('MaxMind database configuration is invalid or missing');
    expect($exception->getConfigurationType())->toBe('database');
});

it('creates maxmind dependency exception with correct data', function (): void {
    $exception = new MaxMindDependencyException('geoip2/geoip2', 'Library missing', 404);

    expect($exception)->toBeInstanceOf(GeolocationException::class);
    expect($exception->getDependency())->toBe('geoip2/geoip2');
    expect($exception->getMessage())->toBe('Library missing');
    expect($exception->getCode())->toBe(404);
});

it('creates maxmind dependency exception with default message', function (): void {
    $exception = new MaxMindDependencyException('some/package');

    expect($exception->getMessage())->toBe("Required dependency 'some/package' is not available. Please install it using composer.");
    expect($exception->getDependency())->toBe('some/package');
});

it('creates request analytics storage exception with correct data', function (): void {
    $requestData = ['path' => '/test', 'ip' => '127.0.0.1'];
    $exception = new RequestAnalyticsStorageException($requestData, 'Database error', 500);

    expect($exception)->toBeInstanceOf(RequestAnalyticsException::class);
    expect($exception->getRequestData())->toBe($requestData);
    expect($exception->getMessage())->toBe('Database error');
    expect($exception->getCode())->toBe(500);
});

it('creates request analytics storage exception with default message', function (): void {
    $exception = new RequestAnalyticsStorageException;

    expect($exception->getMessage())->toBe('Failed to store request analytics data');
    expect($exception->getRequestData())->toBe([]);
});

it('creates bot detection exception with correct data', function (): void {
    $exception = new BotDetectionException('Mozilla/5.0', '192.168.1.1', 'Invalid IP', 400);

    expect($exception)->toBeInstanceOf(RequestAnalyticsException::class);
    expect($exception->getUserAgent())->toBe('Mozilla/5.0');
    expect($exception->getIpAddress())->toBe('192.168.1.1');
    expect($exception->getMessage())->toBe('Invalid IP');
    expect($exception->getCode())->toBe(400);
});

it('creates bot detection exception with default message', function (): void {
    $exception = new BotDetectionException;

    expect($exception->getMessage())->toBe('Bot detection failed');
    expect($exception->getUserAgent())->toBeNull();
    expect($exception->getIpAddress())->toBeNull();
});

it('maintains proper exception hierarchy', function (): void {
    $geolocationException = new GeolocationException;
    $providerException = new GeolocationProviderException('test', '1.1.1.1');
    $configException = new MaxMindConfigurationException('test');
    $dependencyException = new MaxMindDependencyException('test');
    $storageException = new RequestAnalyticsStorageException;
    $botException = new BotDetectionException;

    // All should inherit from RequestAnalyticsException
    expect($geolocationException)->toBeInstanceOf(RequestAnalyticsException::class);
    expect($providerException)->toBeInstanceOf(RequestAnalyticsException::class);
    expect($configException)->toBeInstanceOf(RequestAnalyticsException::class);
    expect($dependencyException)->toBeInstanceOf(RequestAnalyticsException::class);
    expect($storageException)->toBeInstanceOf(RequestAnalyticsException::class);
    expect($botException)->toBeInstanceOf(RequestAnalyticsException::class);

    // Geolocation-related exceptions should inherit from GeolocationException
    expect($providerException)->toBeInstanceOf(GeolocationException::class);
    expect($configException)->toBeInstanceOf(GeolocationException::class);
    expect($dependencyException)->toBeInstanceOf(GeolocationException::class);

    // All should be instances of base Exception
    expect($geolocationException)->toBeInstanceOf(Exception::class);
    expect($providerException)->toBeInstanceOf(Exception::class);
    expect($configException)->toBeInstanceOf(Exception::class);
    expect($dependencyException)->toBeInstanceOf(Exception::class);
    expect($storageException)->toBeInstanceOf(Exception::class);
    expect($botException)->toBeInstanceOf(Exception::class);
});
