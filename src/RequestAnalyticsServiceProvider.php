<?php

namespace MeShaon\RequestAnalytics;

use MeShaon\RequestAnalytics\Commands\RequestAnalyticsCommand;
use MeShaon\RequestAnalytics\Http\Middleware\APIRequestCapture;
use MeShaon\RequestAnalytics\Http\Middleware\WebRequestCapture;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class RequestAnalyticsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('/'),
        ], 'assets');

        $this->publishes([
            __DIR__ . '/../config/request-analytics.php' => config_path('request-analytics.php')
        ], 'config');

        $package
            ->name('laravel-request-analytics')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web')
            ->hasAssets()
            ->hasMigration('create_request_analytics_table')
            ->hasCommand(RequestAnalyticsCommand::class);

        $this->registerMiddlewareAsAliases();
    }

    public function boot()
    {
        $this->pushMiddlewareToPipeline();

        return parent::boot();
    }

    private function registerMiddlewareAsAliases()
    {
        /* @var \Illuminate\Routing\Router */
        $router = $this->app->make('router');

        $router->aliasMiddleware('request-analytics.web', WebRequestCapture::class);
        $router->aliasMiddleware('request-analytics.api', APIRequestCapture::class);
    }

    private function pushMiddlewareToPipeline()
    {
        /* @var \Illuminate\Routing\Router */
        $router = $this->app->make('router');

        if (config('request-analytics.capture.web')) {
            $router->pushMiddlewareToGroup('web', WebRequestCapture::class);
        }

        if (config('request-analytics.capture.api')) {
            $router->pushMiddlewareToGroup('api', APIRequestCapture::class);
        }
    }
}
