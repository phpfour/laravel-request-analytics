# Laravel Request Analytics

[![Latest Version on Packagist](https://img.shields.io/packagist/v/me-shaon/laravel-request-analytics.svg?style=flat-square)](https://packagist.org/packages/me-shaon/laravel-request-analytics)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/me-shaon/laravel-request-analytics/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/me-shaon/laravel-request-analytics/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/me-shaon/laravel-request-analytics/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/me-shaon/laravel-request-analytics/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/me-shaon/laravel-request-analytics.svg?style=flat-square)](https://packagist.org/packages/me-shaon/laravel-request-analytics)

![Laravel request analytics](https://github.com/me-shaon/laravel-request-analytics/blob/main/preview.png?raw=true)

## Overview

Laravel Request Analytics is a comprehensive web analytics solution designed specifically for Laravel applications. This package provides detailed insights into your application's traffic patterns, user behavior, and performance metrics through an intuitive dashboard and powerful API endpoints.

Built with performance and privacy in mind, the package offers intelligent bot detection, IP geolocation services, and GDPR-compliant data handling. Whether you're running a small blog or a large-scale application, Laravel Request Analytics provides the tools you need to understand your audience and optimize user experience.


## Installation

### Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
- MySQL 5.7+ / PostgreSQL 10+ / SQLite 3.8+

### Install via Composer

Install the package using Composer:

```bash
composer require me-shaon/laravel-request-analytics
```

### Database Setup

Publish and run the migrations:

```bash
php artisan vendor:publish --tag="request-analytics-migrations"
php artisan migrate
```

### Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag="request-analytics-config"
```

The configuration file will be published to `config/request-analytics.php` with the following options:

```php
return [
    'database' => [
        'connection' => env('REQUEST_ANALYTICS_DB_CONNECTION', null), // Use default connection if null
        'table' => env('REQUEST_ANALYTICS_TABLE_NAME', 'request_analytics'),
    ],

    'route' => [
        'name' => 'request.analytics',
        'pathname' => env('REQUEST_ANALYTICS_PATHNAME', 'analytics'),
    ],

    'capture' => [
        'web' => true,
        'api' => true,
        'bots' => false, // Set to true to capture bot traffic
    ],

    'middleware' => [
        'web' => [
            'web',
            // 'auth', // Uncomment if using web authentication
            'request-analytics.access',
        ],
        'api' => [
            'api',
            // 'auth:sanctum', // Uncomment if using Sanctum authentication
            'request-analytics.access',
        ],
    ],

    'queue' => [
        'enabled' => env('REQUEST_ANALYTICS_QUEUE_ENABLED', false),
    ],

    'ignore-paths' => [
        env('REQUEST_ANALYTICS_PATHNAME', 'analytics'),
    ],

    'pruning' => [
        'enabled' => env('REQUEST_ANALYTICS_PRUNING_ENABLED', true),
        'days' => env('REQUEST_ANALYTICS_PRUNING_DAYS', 90),
    ],

    'geolocation' => [
        'enabled' => env('REQUEST_ANALYTICS_GEO_ENABLED', true),
        'provider' => env('REQUEST_ANALYTICS_GEO_PROVIDER', 'ipapi'), // ipapi, ipgeolocation, maxmind
        'api_key' => env('REQUEST_ANALYTICS_GEO_API_KEY'),

        // MaxMind specific configuration
        'maxmind' => [
            'type' => env('REQUEST_ANALYTICS_MAXMIND_TYPE', 'webservice'), // webservice or database
            'user_id' => env('REQUEST_ANALYTICS_MAXMIND_USER_ID'),
            'license_key' => env('REQUEST_ANALYTICS_MAXMIND_LICENSE_KEY'),
            'database_path' => env('REQUEST_ANALYTICS_MAXMIND_DB_PATH', storage_path('app/GeoLite2-City.mmdb')),
        ],
    ],

    'privacy' => [
        'anonymize_ip' => env('REQUEST_ANALYTICS_ANONYMIZE_IP', false),
        'respect_dnt' => env('REQUEST_ANALYTICS_RESPECT_DNT', true), // Respect Do Not Track header
    ],

    'cache' => [
        'ttl' => env('REQUEST_ANALYTICS_CACHE_TTL', 5), // Cache TTL in minutes
    ],
];
```
### Optional Assets & Views

Publish dashboard assets (CSS, JS):
```bash
php artisan vendor:publish --tag="request-analytics-assets"
```

Publish views for customization:
```bash
php artisan vendor:publish --tag="request-analytics-views"
```

### Automated Data Pruning

The package includes automatic data cleanup to manage database size. Configure pruning in your scheduler:

**Laravel 11+**

Add to `routes/console.php`:
```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('model:prune', [
    '--model' => 'MeShaon\RequestAnalytics\Models\RequestAnalytics',
])->daily();
```

Or in `bootstrap/app.php`:
```php
use Illuminate\Console\Scheduling\Schedule;

->withSchedule(function (Schedule $schedule) {
    $schedule->command('model:prune', [
        '--model' => 'MeShaon\RequestAnalytics\Models\RequestAnalytics',
    ])->daily();
})
```

**Laravel 10 and below**

Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule): void
{
    $schedule->command('model:prune', [
        '--model' => 'MeShaon\RequestAnalytics\Models\RequestAnalytics',
    ])->daily();
}
```

## Key Features

### Analytics & Reporting
- **Real-time Dashboard**: Interactive charts and metrics with responsive design
- **Comprehensive Metrics**: Page views, unique visitors, bounce rates, and session duration
- **Traffic Analysis**: Detailed breakdown of traffic sources and user pathways
- **Performance Insights**: Load times and user interaction patterns

### Privacy & Compliance
- **GDPR Compliance**: Built-in privacy controls and data anonymization
- **IP Anonymization**: Configurable IP address masking for user privacy
- **Do Not Track Support**: Respects browser DNT headers automatically
- **Data Retention**: Configurable automatic data pruning and cleanup

### Intelligence & Detection  
- **Advanced Bot Detection**: Filters search engines, social bots, and crawlers
- **Device Recognition**: Browser, OS, and device type identification
- **Geolocation Services**: Multiple provider support (IP-API, IPGeolocation, MaxMind)
- **Visitor Tracking**: Cookie-based unique visitor identification

### Performance & Integration
- **High Performance**: Optimized database queries with intelligent caching
- **Queue Support**: Background processing for high-traffic applications  
- **REST API**: Complete programmatic access to analytics data
- **Laravel Integration**: Seamless integration with Laravel's authentication and middleware systems

## Usage

## Configuration Options

### Route Configuration
- `route.name`: Named route identifier (default: `request.analytics`)
- `route.pathname`: URL path for dashboard access (default: `analytics`)

### Data Capture Settings
- `capture.web`: Track web requests (default: `true`)
- `capture.api`: Track API requests (default: `true`) 
- `capture.bots`: Include bot traffic in analytics (default: `false`)

### Queue Processing
- `queue.enabled`: Process analytics data in background jobs for better performance

### Path Filtering
- `ignore-paths`: Array of paths to exclude from tracking (e.g., admin routes, health checks)

### Data Retention
- `pruning.enabled`: Automatic data cleanup (default: `true`)
- `pruning.days`: Days to retain data (default: 90)

### Geolocation Services

The package supports multiple geolocation providers:

#### IP-API (Default - Free)
```php
'geolocation' => [
    'enabled' => true,
    'provider' => 'ipapi',
    'api_key' => null, // Not required
]
```
- No API key required
- 45 requests per minute limit
- Includes country, region, city, timezone

#### IPGeolocation  
```php
'geolocation' => [
    'enabled' => true,
    'provider' => 'ipgeolocation',
    'api_key' => env('REQUEST_ANALYTICS_GEO_API_KEY'),
]
```
- Requires API key from [ipgeolocation.io](https://ipgeolocation.io)
- Higher rate limits and accuracy
- Additional ISP and threat intelligence data

#### MaxMind
```php
'geolocation' => [
    'enabled' => true,
    'provider' => 'maxmind',
    'api_key' => env('REQUEST_ANALYTICS_GEO_API_KEY'),
]
```
- Requires GeoIP2 database or web service account
- Highest accuracy and performance
- Enterprise-grade IP intelligence

### Privacy & Compliance
```php
'privacy' => [
    'anonymize_ip' => env('REQUEST_ANALYTICS_ANONYMIZE_IP', false),
    'respect_dnt' => env('REQUEST_ANALYTICS_RESPECT_DNT', true),
]
```

- **IP Anonymization**: Masks the last octet of IPv4 addresses (192.168.1.xxx)
- **Do Not Track**: Automatically respects browser DNT headers

### Bot Detection

Advanced bot detection includes:
- **Search Engines**: Google, Bing, Yahoo, DuckDuckGo, Baidu
- **Social Media**: Facebook, Twitter, LinkedIn, Pinterest crawlers  
- **SEO Tools**: Ahrefs, SEMrush, Moz, Screaming Frog
- **Monitoring**: Pingdom, UptimeRobot, StatusCake
- **Development**: curl, wget, Postman, Insomnia

## Dashboard Access & Usage

### Dashboard Access
The analytics dashboard is available at `/analytics` by default. Access the dashboard through your configured route after authentication.

### Access Control
Implement the `CanAccessAnalyticsDashboard` interface in your User model to control dashboard access:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use MeShaon\RequestAnalytics\Contracts\CanAccessAnalyticsDashboard;

class User extends Authenticatable implements CanAccessAnalyticsDashboard
{
    public function canAccessAnalyticsDashboard(): bool
    {
        // Example: Only allow admin users
        return $this->role === 'admin';
        
        // Or check specific permissions
        // return $this->can('view-analytics');
        
        // Or allow all authenticated users
        // return true;
    }
}
```

### Dashboard Features
- **Real-time Metrics**: Live visitor count, page views, and bounce rate
- **Interactive Charts**: Traffic trends, geographic distribution, device breakdown
- **Top Pages**: Most visited pages with performance metrics
- **Visitor Insights**: Browser, OS, and device analytics
- **Traffic Sources**: Referrer analysis and search engine traffic
- **Performance Data**: Page load times and user engagement metrics

## API Documentation

The package provides a comprehensive REST API for programmatic access to analytics data.

### Endpoints

#### GET /api/v1/analytics/overview
Retrieve comprehensive analytics overview with summary statistics and chart data.

**Parameters:**
- `period` (optional): `today`, `yesterday`, `7days`, `30days`, `90days` (default: `30days`)
- `with_percentages` (optional): Include percentage changes (default: `false`)

**Response:**
```json
{
    "success": true,
    "data": {
        "summary": {
            "total_page_views": 15420,
            "unique_visitors": 8760,
            "bounce_rate": 65.4,
            "avg_session_duration": 180
        },
        "charts": {
            "traffic_trend": [...],
            "top_pages": [...],
            "geographic_data": [...],
            "device_breakdown": [...]
        }
    }
}
```

#### GET /api/v1/analytics/visitors
Get paginated visitor data with detailed information.

**Parameters:**
- `page` (optional): Page number for pagination (default: `1`)
- `per_page` (optional): Items per page, max 100 (default: `15`)
- `period` (optional): Time period filter
- `country` (optional): Filter by country code
- `device` (optional): Filter by device type

**Response:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "ip_address": "192.168.1.***",
                "country": "United States",
                "city": "New York",
                "device": "Desktop",
                "browser": "Chrome 91",
                "operating_system": "Windows 10",
                "visited_at": "2024-01-15T14:30:00Z"
            }
        ],
        "total": 8760,
        "per_page": 15,
        "last_page": 584
    }
}
```

#### GET /api/v1/analytics/page-views
Retrieve paginated page view data with performance metrics.

**Parameters:**
- `page` (optional): Page number for pagination
- `per_page` (optional): Items per page, max 100  
- `period` (optional): Time period filter
- `url` (optional): Filter by specific URL pattern

**Response:**
```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "url": "/products/smartphone",
                "title": "Latest Smartphones",
                "method": "GET",
                "status_code": 200,
                "load_time": 1.25,
                "referrer": "https://google.com",
                "user_agent": "Mozilla/5.0...",
                "created_at": "2024-01-15T14:30:00Z"
            }
        ],
        "total": 15420,
        "per_page": 15
    }
}
```

### Error Handling
API responses follow consistent error format:

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "period": ["The selected period is invalid."]
    }
}
```

**Common HTTP Status Codes:**
- `200`: Success
- `400`: Bad Request (validation errors)
- `401`: Unauthorized (invalid or missing token)
- `403`: Forbidden (insufficient permissions)  
- `429`: Too Many Requests (rate limited)
- `500`: Internal Server Error

## Testing

```bash
vendor/bin/phpunit --no-coverage
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ahmed shamim](https://github.com/me-shaon)
- [Omar Faruque](https://github.com/OmarFaruk-0x01)
- [Md Abul Hassan](https://github.com/imabulhasan99)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
