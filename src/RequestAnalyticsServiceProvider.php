<?php

namespace MeShaon\RequestAnalytics;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use MeShaon\RequestAnalytics\Commands\RequestAnalyticsCommand;

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
            ->hasMigration('create_laravel-request-analytics_table')
            ->hasCommand(RequestAnalyticsCommand::class);
    }
}
