<?php

use MeShaon\RequestAnalytics\Exceptions\BotDetectionException;
use MeShaon\RequestAnalytics\Services\BotDetectionService;

it('throws exception for invalid IP address format', function (): void {
    $service = new BotDetectionService;

    expect(fn (): bool => $service->isBot('Mozilla/5.0', 'invalid-ip-address'))
        ->toThrow(BotDetectionException::class, 'Invalid IP address format during bot detection');
});

it('handles valid IP addresses without throwing exceptions', function (): void {
    $service = new BotDetectionService;

    // Should not throw an exception for valid IPs
    $result = $service->isBot('Mozilla/5.0', '192.168.1.1');
    expect($result)->toBeBool();

    $result = $service->isBot('Mozilla/5.0', '8.8.8.8');
    expect($result)->toBeBool();
});

it('detects bots correctly without exceptions', function (): void {
    $service = new BotDetectionService;

    // Should detect a bot
    $result = $service->isBot('Googlebot/2.1');
    expect($result)->toBeTrue();

    // Should not detect a regular browser
    $result = $service->isBot('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    expect($result)->toBeFalse();
});

it('returns correct bot name without exceptions', function (): void {
    $service = new BotDetectionService;

    $botName = $service->getBotName('Googlebot/2.1');
    expect($botName)->toBe('Google');

    $botName = $service->getBotName('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    expect($botName)->toBeNull();
});
