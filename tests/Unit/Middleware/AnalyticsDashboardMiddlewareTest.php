<?php

declare(strict_types=1);

namespace MeShaon\RequestAnalytics\Tests\Unit\Middleware;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MeShaon\RequestAnalytics\Contracts\CanAccessAnalyticsDashboard;
use MeShaon\RequestAnalytics\Http\Middleware\AnalyticsDashboardMiddleware;
use MeShaon\RequestAnalytics\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\Test;

class AnalyticsDashboardMiddlewareTest extends TestCase
{
    private AnalyticsDashboardMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new AnalyticsDashboardMiddleware;
    }

    #[Test]
    public function it_returns_json_403_when_no_user_and_expects_json(): void
    {
        $request = Request::create('/api/analytics');
        $request->headers->set('Accept', 'application/json');

        $next = fn($req): Response => new Response('Should not reach here');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(403, $response->getStatusCode());
        $responseData = json_decode((string) $response->getContent(), true);
        $this->assertEquals('Unauthorized', $responseData['message']);
    }

    #[Test]
    public function it_aborts_403_when_no_user_and_expects_html(): void
    {
        $request = Request::create('/analytics');

        $next = fn($req): Response => new Response('Should not reach here');

        $this->expectException(HttpException::class);

        $this->middleware->handle($request, $next);
    }

    #[Test]
    public function it_returns_json_403_when_user_does_not_implement_interface_and_expects_json(): void
    {
        $user = Mockery::mock();
        $request = Request::create('/api/analytics');
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn() => $user);

        $next = fn($req): Response => new Response('Should not reach here');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(403, $response->getStatusCode());
        $responseData = json_decode((string) $response->getContent(), true);
        $this->assertEquals('Unauthorized', $responseData['message']);
    }

    #[Test]
    public function it_aborts_403_when_user_does_not_implement_interface_and_expects_html(): void
    {
        $user = Mockery::mock();
        $request = Request::create('/analytics');
        $request->setUserResolver(fn() => $user);

        $next = fn($req): Response => new Response('Should not reach here');

        $this->expectException(HttpException::class);

        $this->middleware->handle($request, $next);
    }

    #[Test]
    public function it_returns_json_403_when_user_cannot_access_dashboard_and_expects_json(): void
    {
        $user = Mockery::mock(CanAccessAnalyticsDashboard::class);
        $user->shouldReceive('canAccessAnalyticsDashboard')->andReturn(false);

        $request = Request::create('/api/analytics');
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn() => $user);

        $next = fn($req): Response => new Response('Should not reach here');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(403, $response->getStatusCode());
        $responseData = json_decode((string) $response->getContent(), true);
        $this->assertEquals('Access denied', $responseData['message']);
    }

    #[Test]
    public function it_aborts_403_when_user_cannot_access_dashboard_and_expects_html(): void
    {
        $user = Mockery::mock(CanAccessAnalyticsDashboard::class);
        $user->shouldReceive('canAccessAnalyticsDashboard')->andReturn(false);

        $request = Request::create('/analytics');
        $request->setUserResolver(fn() => $user);

        $next = fn($req): Response => new Response('Should not reach here');

        $this->expectException(HttpException::class);

        $this->middleware->handle($request, $next);
    }

    #[Test]
    public function it_allows_access_when_user_can_access_dashboard(): void
    {
        $user = Mockery::mock(CanAccessAnalyticsDashboard::class);
        $user->shouldReceive('canAccessAnalyticsDashboard')->andReturn(true);

        $request = Request::create('/analytics');
        $request->setUserResolver(fn() => $user);

        $next = fn($req): Response => new Response('Dashboard content');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Dashboard content', $response->getContent());
    }

    #[Test]
    public function it_allows_json_access_when_user_can_access_dashboard(): void
    {
        $user = Mockery::mock(CanAccessAnalyticsDashboard::class);
        $user->shouldReceive('canAccessAnalyticsDashboard')->andReturn(true);

        $request = Request::create('/api/analytics');
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(fn() => $user);

        $next = fn($req): Response => new Response('{"data": "success"}');

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"data": "success"}', $response->getContent());
    }
}
