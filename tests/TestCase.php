<?php

namespace MeShaon\RequestAnalytics\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use MeShaon\RequestAnalytics\RequestAnalyticsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'MeShaon\\RequestAnalytics\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            RequestAnalyticsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_laravel-request-analytics_table.php.stub';
        $migration->up();
        */
    }
}
