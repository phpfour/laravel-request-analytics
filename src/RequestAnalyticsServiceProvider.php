<?php

namespace MeShaon\RequestAnalytics;

use MeShaon\RequestAnalytics\Commands\RequestAnalyticsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class RequestAnalyticsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('/'),
        ], 'assets');

        $package
            ->name('laravel-request-analytics')
            ->hasConfigFile()
            ->hasViews()
            ->hasRoute('web')
            ->hasAssets()
            ->hasMigration('create_request_analytics_table')
            ->hasCommand(RequestAnalyticsCommand::class);
    }
}
