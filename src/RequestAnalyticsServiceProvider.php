<?php

namespace MeShaon\RequestAnalytics;

use MeShaon\RequestAnalytics\Commands\RequestAnalyticsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class RequestAnalyticsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-request-analytics')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_request_analytics_table')
            ->hasCommand(RequestAnalyticsCommand::class);
    }
}
