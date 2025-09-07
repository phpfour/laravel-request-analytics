<?php

declare(strict_types=1);

namespace MeShaon\RequestAnalytics\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use MeShaon\RequestAnalytics\Services\VisitorTrackingService;
use MeShaon\RequestAnalytics\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class VisitorTrackingServiceTest extends TestCase
{
    private VisitorTrackingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new VisitorTrackingService;
    }

    #[Test]
    public function it_returns_existing_visitor_id_from_cookie(): void
    {
        $existingVisitorId = 'existing_visitor_123';

        $request = Request::create('/test');
        $request->cookies->set('ra_visitor_id', $existingVisitorId);

        $result = $this->service->getVisitorId($request);

        $this->assertEquals($existingVisitorId, $result);
    }

    #[Test]
    public function it_generates_new_visitor_id_when_no_cookie_exists(): void
    {
        Cookie::shouldReceive('queue')->once();

        $request = Request::create('/test');
        $request->headers->set('User-Agent', 'Test Browser');
        $request->headers->set('Accept-Language', 'en-US');
        $request->headers->set('Accept-Encoding', 'gzip, deflate');

        $result = $this->service->getVisitorId($request);

        $this->assertIsString($result);
        $this->assertEquals(64, strlen($result)); // SHA256 hash length
    }

    #[Test]
    public function it_generates_visitor_id_based_on_request_fingerprint(): void
    {
        $request1 = Request::create('/test');
        $request1->headers->set('User-Agent', 'Browser 1');
        $request1->headers->set('Accept-Language', 'en-US');
        $request1->server->set('REMOTE_ADDR', '192.168.1.1');

        $request2 = Request::create('/test');
        $request2->headers->set('User-Agent', 'Browser 2');
        $request2->headers->set('Accept-Language', 'fr-FR');
        $request2->server->set('REMOTE_ADDR', '192.168.1.2');

        $visitorId1 = $this->service->generateVisitorId($request1);
        $visitorId2 = $this->service->generateVisitorId($request2);

        $this->assertNotEquals($visitorId1, $visitorId2);
        $this->assertEquals(64, strlen($visitorId1));
        $this->assertEquals(64, strlen($visitorId2));
    }

    #[Test]
    public function it_creates_fingerprint_from_request_headers(): void
    {
        $request = Request::create('/test');
        $request->headers->set('User-Agent', 'Test Browser');
        $request->headers->set('Accept-Language', 'en-US');
        $request->headers->set('Accept-Encoding', 'gzip');
        $request->server->set('REMOTE_ADDR', '192.168.1.1');

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('createFingerprint');
        $method->setAccessible(true);

        $fingerprint = $method->invoke($this->service, $request);

        $this->assertIsString($fingerprint);
        $this->assertStringContainsString('Test Browser', $fingerprint);
        $this->assertStringContainsString('en-US', $fingerprint);
        $this->assertStringContainsString('gzip', $fingerprint);
        $this->assertStringContainsString('192.168.1.1', $fingerprint);
    }

    #[Test]
    public function it_handles_empty_headers_in_fingerprint(): void
    {
        $request = Request::create('/test');

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('createFingerprint');
        $method->setAccessible(true);

        $fingerprint = $method->invoke($this->service, $request);

        $this->assertIsString($fingerprint);
    }

    #[Test]
    public function it_gets_session_id_from_request_session(): void
    {
        $request = Request::create('/test');

        // Start session manually for testing
        $this->app['session']->start();
        $request->setLaravelSession($this->app['session']);

        $result = $this->service->getSessionId($request);

        $this->assertEquals($this->app['session']->getId(), $result);
    }

    #[Test]
    public function it_generates_session_id_when_no_session_exists(): void
    {
        $request = Request::create('/test');
        $request->headers->set('User-Agent', 'Test Browser');

        $result = $this->service->getSessionId($request);

        $this->assertIsString($result);
        $this->assertEquals(64, strlen($result)); // SHA256 hash length
    }

    #[Test]
    public function it_generates_consistent_session_id_within_same_time_window(): void
    {
        $request1 = Request::create('/test');
        $request1->headers->set('User-Agent', 'Test Browser');
        $request1->cookies->set('ra_visitor_id', 'same_visitor');

        $request2 = Request::create('/test');
        $request2->headers->set('User-Agent', 'Test Browser');
        $request2->cookies->set('ra_visitor_id', 'same_visitor');

        $sessionId1 = $this->service->getSessionId($request1);
        $sessionId2 = $this->service->getSessionId($request2);

        $this->assertEquals($sessionId1, $sessionId2);
    }

    #[Test]
    public function it_generates_session_id_based_on_visitor_and_time(): void
    {
        $request = Request::create('/test');
        $request->headers->set('User-Agent', 'Test Browser');

        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateSessionId');
        $method->setAccessible(true);

        $sessionId = $method->invoke($this->service, $request);

        $this->assertIsString($sessionId);
        $this->assertEquals(64, strlen($sessionId));
    }

    #[Test]
    public function it_detects_new_visitor_when_no_cookie(): void
    {
        $request = Request::create('/test');

        $result = $this->service->isNewVisitor($request);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_detects_new_visitor_when_cookie_does_not_exist(): void
    {
        $request = Request::create('/test');
        $request->cookies->set('some_other_cookie', 'value');

        $result = $this->service->isNewVisitor($request);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_detects_returning_visitor_when_cookie_exists(): void
    {
        $request = Request::create('/test');
        $request->cookies->set('ra_visitor_id', 'existing_visitor_123');

        $result = $this->service->isReturningVisitor($request);

        $this->assertTrue($result);
    }

    #[Test]
    public function it_does_not_detect_returning_visitor_when_no_cookie(): void
    {
        $request = Request::create('/test');

        $result = $this->service->isReturningVisitor($request);

        $this->assertFalse($result);
    }

    #[Test]
    public function new_visitor_and_returning_visitor_are_mutually_exclusive(): void
    {
        $newVisitorRequest = Request::create('/test');
        $returningVisitorRequest = Request::create('/test');
        $returningVisitorRequest->cookies->set('ra_visitor_id', 'visitor_123');

        $this->assertTrue($this->service->isNewVisitor($newVisitorRequest));
        $this->assertFalse($this->service->isReturningVisitor($newVisitorRequest));

        $this->assertFalse($this->service->isNewVisitor($returningVisitorRequest));
        $this->assertTrue($this->service->isReturningVisitor($returningVisitorRequest));
    }
}
