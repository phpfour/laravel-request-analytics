<?php

declare(strict_types=1);

namespace MeShaon\RequestAnalytics\Tests\Unit;

use MeShaon\RequestAnalytics\Exceptions\BotDetectionException;
use MeShaon\RequestAnalytics\Services\BotDetectionService;
use MeShaon\RequestAnalytics\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class BotDetectionServiceTest extends TestCase
{
    private BotDetectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BotDetectionService;
    }

    #[Test]
    public function it_detects_null_user_agent_as_bot(): void
    {
        $this->assertTrue($this->service->isBot(null));
    }

    #[Test]
    public function it_detects_empty_user_agent_as_bot(): void
    {
        $this->assertTrue($this->service->isBot(''));
        $this->assertTrue($this->service->isBot('0'));
    }

    #[Test]
    #[DataProvider('botUserAgentProvider')]
    public function it_detects_bot_user_agents(string $userAgent): void
    {
        $this->assertTrue($this->service->isBot($userAgent));
    }

    public static function botUserAgentProvider(): array
    {
        return [
            ['Googlebot/2.1'],
            ['Mozilla/5.0 (compatible; bingbot/2.0)'],
            ['facebookexternalhit/1.1'],
            ['Twitterbot/1.0'],
            ['LinkedInBot/1.0'],
            ['WhatsApp/2.0'],
            ['Slackbot-LinkExpanding 1.0'],
            ['Chrome-Lighthouse'],
            ['curl/7.64.1'],
            ['wget/1.20.1'],
            ['python-requests/2.25.1'],
            ['PostmanRuntime/7.26.8'],
            ['Spider-Bot'],
            ['Web Crawler'],
        ];
    }

    #[Test]
    #[DataProvider('humanUserAgentProvider')]
    public function it_detects_human_user_agents(string $userAgent): void
    {
        $this->assertFalse($this->service->isBot($userAgent));
    }

    public static function humanUserAgentProvider(): array
    {
        return [
            ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'],
            ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'],
            ['Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0'],
            ['Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15'],
            ['Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36 Edg/91.0.864.59'],
            ['Opera/9.80 (Windows NT 6.1; WOW64) Presto/2.12.388 Version/12.18'],
        ];
    }

    #[Test]
    #[DataProvider('suspiciousUserAgentProvider')]
    public function it_detects_suspicious_user_agents_without_browser_indicators(string $userAgent): void
    {
        $this->assertTrue($this->service->isBot($userAgent));
    }

    public static function suspiciousUserAgentProvider(): array
    {
        return [
            ['Java/1.8.0_271'],
            ['Go-http-client/1.1'],
            ['Apache-HttpClient/4.5.13'],
            ['okhttp/4.9.1'],
            ['node-fetch/1.0'],
            ['axios/0.21.1'],
        ];
    }

    #[Test]
    #[DataProvider('botIpProvider')]
    public function it_detects_bot_ips(string $ip): void
    {
        $this->assertTrue($this->service->isBot('Mozilla/5.0 (compatible)', $ip));
    }

    public static function botIpProvider(): array
    {
        return [
            ['66.249.64.1'], // Google
            ['31.13.24.1'],  // Facebook
            ['199.16.156.1'], // Twitter
            ['40.77.167.1'],  // Microsoft/Bing
        ];
    }

    #[Test]
    #[DataProvider('humanIpProvider')]
    public function it_does_not_detect_human_ips_as_bots(string $ip): void
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
        $this->assertFalse($this->service->isBot($userAgent, $ip));
    }

    public static function humanIpProvider(): array
    {
        return [
            ['192.168.1.1'],
            ['10.0.0.1'],
            ['172.16.0.1'],
            ['8.8.8.8'],
            ['1.1.1.1'],
        ];
    }

    #[Test]
    public function it_throws_exception_for_invalid_ip_addresses(): void
    {
        $this->expectException(BotDetectionException::class);
        $this->expectExceptionMessage('Invalid IP address format during bot detection');

        $this->service->isBot('Mozilla/5.0', 'invalid.ip.address');
    }

    #[Test]
    public function it_handles_ipv6_addresses_gracefully(): void
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36';
        $this->assertFalse($this->service->isBot($userAgent, '2001:db8::1'));
    }

    #[Test]
    public function it_handles_single_ip_addresses_in_ranges(): void
    {
        $reflectionClass = new \ReflectionClass($this->service);
        $method = $reflectionClass->getMethod('ipInRange');
        $method->setAccessible(true);

        $this->assertTrue($method->invoke($this->service, '192.168.1.1', '192.168.1.1'));
        $this->assertFalse($method->invoke($this->service, '192.168.1.2', '192.168.1.1'));
    }

    #[Test]
    #[DataProvider('botNameProvider')]
    public function it_gets_correct_bot_names(string $userAgent, string $expectedName): void
    {
        $this->assertEquals($expectedName, $this->service->getBotName($userAgent));
    }

    public static function botNameProvider(): array
    {
        return [
            ['Googlebot/2.1', 'Google'],
            ['Mozilla/5.0 (compatible; bingbot/2.0)', 'Bing'],
            ['facebookexternalhit/1.1', 'Facebook'],
            ['Twitterbot/1.0', 'Twitter'],
            ['LinkedInBot/1.0', 'LinkedIn'],
            ['WhatsApp/2.0', 'WhatsApp'],
            ['Slackbot-LinkExpanding 1.0', 'Slack'],
            ['Chrome-Lighthouse', 'Google Lighthouse'],
            ['AhrefsBot/7.0', 'Ahrefs'],
        ];
    }

    #[Test]
    public function it_returns_null_for_unknown_bot_names(): void
    {
        $this->assertNull($this->service->getBotName('Unknown Bot/1.0'));
        $this->assertNull($this->service->getBotName(null));
        $this->assertNull($this->service->getBotName(''));
        $this->assertNull($this->service->getBotName('0'));
    }

    #[Test]
    public function it_returns_null_for_human_user_agents_bot_name(): void
    {
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36';
        $this->assertNull($this->service->getBotName($userAgent));
    }

    #[Test]
    public function bot_detection_exception_contains_user_agent_and_ip(): void
    {
        $userAgent = 'test-agent';
        $ip = 'test-ip';

        $exception = new BotDetectionException($userAgent, $ip, 'Test message');

        $this->assertEquals($userAgent, $exception->getUserAgent());
        $this->assertEquals($ip, $exception->getIpAddress());
        $this->assertEquals('Test message', $exception->getMessage());
    }

    #[Test]
    public function bot_detection_exception_uses_default_message(): void
    {
        $exception = new BotDetectionException;

        $this->assertEquals('Bot detection failed', $exception->getMessage());
        $this->assertNull($exception->getUserAgent());
        $this->assertNull($exception->getIpAddress());
    }
}
