<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MeShaon\RequestAnalytics\Models\RequestAnalytics;

beforeEach(function (): void {
    Config::set('request-analytics.capture.web', true);
    Config::set('request-analytics.capture.api', true);
    Config::set('request-analytics.capture.bots', false);

    // Set up a basic route for testing
    $this->app['router']->get('/', fn () => response('Hello World'));

    // Create the default table for basic tests
    if (! Schema::hasTable('request_analytics')) {
        Schema::create('request_analytics', function (Blueprint $table): void {
            $table->id();
            $table->string('path');
            $table->string('page_title');
            $table->string('ip_address');
            $table->string('operating_system')->nullable();
            $table->string('browser')->nullable();
            $table->string('device')->nullable();
            $table->string('screen')->nullable();
            $table->string('referrer')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('language')->nullable();
            $table->tinyText('query_params')->nullable();
            $table->string('session_id');
            $table->string('visitor_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('http_method');
            $table->string('request_category');
            $table->bigInteger('response_time')->nullable();
            $table->timestamp('visited_at');
        });
    }
});

afterEach(function (): void {
    // Clean up any test tables that might have been created
    $customConnection = config('database.connections.secondary');
    if ($customConnection) {
        Schema::connection('secondary')->dropIfExists('custom_analytics_table');
        Schema::connection('secondary')->dropIfExists('test_request_analytics');
        Schema::connection('secondary')->dropIfExists('custom_request_analytics');
        Schema::connection('secondary')->dropIfExists('test_analytics');
    }

    Schema::dropIfExists('custom_analytics_table');
    Schema::dropIfExists('test_request_analytics');
    Schema::dropIfExists('request_analytics');
});

it('uses default table name when none is configured', function (): void {
    Config::set('request-analytics.database.table', null);

    $model = new RequestAnalytics;

    expect($model->getTable())->toBe('request_analytics');
});

it('uses custom table name when configured', function (): void {
    Config::set('request-analytics.database.table', 'custom_analytics_table');

    // Create the custom table for testing
    Schema::create('custom_analytics_table', function (Blueprint $table): void {
        $table->id();
        $table->string('path');
        $table->string('page_title');
        $table->string('ip_address');
        $table->string('operating_system')->nullable();
        $table->string('browser')->nullable();
        $table->string('device')->nullable();
        $table->string('screen')->nullable();
        $table->string('referrer')->nullable();
        $table->string('country')->nullable();
        $table->string('city')->nullable();
        $table->string('language')->nullable();
        $table->tinyText('query_params')->nullable();
        $table->string('session_id');
        $table->string('visitor_id')->nullable()->index();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('http_method');
        $table->string('request_category');
        $table->bigInteger('response_time')->nullable();
        $table->timestamp('visited_at');
    });

    $model = new RequestAnalytics;

    expect($model->getTable())->toBe('custom_analytics_table');
});

it('uses default connection when none is configured', function (): void {
    Config::set('request-analytics.database.connection', null);

    $model = new RequestAnalytics;

    // When connection is null, Laravel uses the default connection (which is null when not explicitly set)
    expect($model->getConnectionName())->toBeNull();
});

it('uses custom connection when configured', function (): void {
    // Set up a secondary database connection for testing
    Config::set('database.connections.secondary', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    Config::set('request-analytics.database.connection', 'secondary');

    $model = new RequestAnalytics;

    expect($model->getConnectionName())->toBe('secondary');
});

it('creates table with custom connection and table name', function (): void {
    // Set up a secondary database connection
    Config::set('database.connections.secondary', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    Config::set('request-analytics.database.connection', 'secondary');
    Config::set('request-analytics.database.table', 'test_request_analytics');

    // Create the table on the secondary connection
    Schema::connection('secondary')->create('test_request_analytics', function (Blueprint $table): void {
        $table->id();
        $table->string('path');
        $table->string('page_title');
        $table->string('ip_address');
        $table->string('operating_system')->nullable();
        $table->string('browser')->nullable();
        $table->string('device')->nullable();
        $table->string('screen')->nullable();
        $table->string('referrer')->nullable();
        $table->string('country')->nullable();
        $table->string('city')->nullable();
        $table->string('language')->nullable();
        $table->tinyText('query_params')->nullable();
        $table->string('session_id');
        $table->string('visitor_id')->nullable()->index();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('http_method');
        $table->string('request_category');
        $table->bigInteger('response_time')->nullable();
        $table->timestamp('visited_at');
    });

    $model = new RequestAnalytics;

    expect($model->getConnectionName())->toBe('secondary');
    expect($model->getTable())->toBe('test_request_analytics');
    expect(Schema::connection('secondary')->hasTable('test_request_analytics'))->toBeTrue();
});

it('can insert records using custom connection and table name', function (): void {
    // Set up a secondary database connection
    Config::set('database.connections.secondary', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    Config::set('request-analytics.database.connection', 'secondary');
    Config::set('request-analytics.database.table', 'test_request_analytics');

    // Create the table on the secondary connection
    Schema::connection('secondary')->create('test_request_analytics', function (Blueprint $table): void {
        $table->id();
        $table->string('path');
        $table->string('page_title');
        $table->string('ip_address');
        $table->string('operating_system')->nullable();
        $table->string('browser')->nullable();
        $table->string('device')->nullable();
        $table->string('screen')->nullable();
        $table->string('referrer')->nullable();
        $table->string('country')->nullable();
        $table->string('city')->nullable();
        $table->string('language')->nullable();
        $table->tinyText('query_params')->nullable();
        $table->string('session_id');
        $table->string('visitor_id')->nullable()->index();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('http_method');
        $table->string('request_category');
        $table->bigInteger('response_time')->nullable();
        $table->timestamp('visited_at');
    });

    // Create a new model instance and insert data
    $model = new RequestAnalytics([
        'path' => '/test',
        'page_title' => 'Test Page',
        'ip_address' => '127.0.0.1',
        'session_id' => 'test-session',
        'http_method' => 'GET',
        'request_category' => 'web',
        'visited_at' => now(),
    ]);

    $model->save();

    // Verify the record was saved to the correct connection and table
    expect($model->getConnectionName())->toBe('secondary');
    expect($model->getTable())->toBe('test_request_analytics');
    expect($model->exists)->toBeTrue();

    // Verify using direct database query
    $recordExists = DB::connection('secondary')
        ->table('test_request_analytics')
        ->where('path', '/test')
        ->where('http_method', 'GET')
        ->where('request_category', 'web')
        ->exists();

    expect($recordExists)->toBeTrue();
});

it('handles migration with custom connection and table name', function (): void {
    // Set up a secondary database connection
    Config::set('database.connections.secondary', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    Config::set('request-analytics.database.connection', 'secondary');
    Config::set('request-analytics.database.table', 'custom_request_analytics');

    // Simulate running the migration
    $tableName = config('request-analytics.database.table');
    $connection = config('request-analytics.database.connection');

    Schema::connection($connection)->create($tableName, function (Blueprint $table): void {
        $table->id();
        $table->string('path');
        $table->string('page_title');
        $table->string('ip_address');
        $table->string('operating_system')->nullable();
        $table->string('browser')->nullable();
        $table->string('device')->nullable();
        $table->string('screen')->nullable();
        $table->string('referrer')->nullable();
        $table->string('country')->nullable();
        $table->string('city')->nullable();
        $table->string('language')->nullable();
        $table->tinyText('query_params')->nullable();
        $table->string('session_id');
        $table->string('visitor_id')->nullable()->index();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('http_method');
        $table->string('request_category');
        $table->bigInteger('response_time')->nullable();
        $table->timestamp('visited_at');
    });

    expect(Schema::connection('secondary')->hasTable('custom_request_analytics'))->toBeTrue();

    // Test that we can also drop the table (down migration)
    Schema::connection($connection)->dropIfExists($tableName);
    expect(Schema::connection('secondary')->hasTable('custom_request_analytics'))->toBeFalse();
});

it('ensures model queries use the correct connection and table', function (): void {
    // Set up a secondary database connection
    Config::set('database.connections.secondary', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    Config::set('request-analytics.database.connection', 'secondary');
    Config::set('request-analytics.database.table', 'test_analytics');

    // Create the table
    Schema::connection('secondary')->create('test_analytics', function (Blueprint $table): void {
        $table->id();
        $table->string('path');
        $table->string('page_title');
        $table->string('ip_address');
        $table->string('operating_system')->nullable();
        $table->string('browser')->nullable();
        $table->string('device')->nullable();
        $table->string('screen')->nullable();
        $table->string('referrer')->nullable();
        $table->string('country')->nullable();
        $table->string('city')->nullable();
        $table->string('language')->nullable();
        $table->tinyText('query_params')->nullable();
        $table->string('session_id');
        $table->string('visitor_id')->nullable()->index();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('http_method');
        $table->string('request_category');
        $table->bigInteger('response_time')->nullable();
        $table->timestamp('visited_at');
    });

    // Insert a test record directly
    DB::connection('secondary')->table('test_analytics')->insert([
        'path' => '/test',
        'page_title' => 'Test Page',
        'ip_address' => '127.0.0.1',
        'session_id' => 'test-session',
        'http_method' => 'GET',
        'request_category' => 'web',
        'visited_at' => now(),
    ]);

    // Create model instance and verify it uses the correct connection and table
    $model = new RequestAnalytics;
    $records = $model->where('path', '/test')->get();

    expect($records)->toHaveCount(1);
    expect($records->first()->path)->toBe('/test');
    expect($records->first()->getConnectionName())->toBe('secondary');
    expect($records->first()->getTable())->toBe('test_analytics');
});
