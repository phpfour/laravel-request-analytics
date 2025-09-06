<?php

use Illuminate\Support\Facades\Route;
use MeShaon\RequestAnalytics\Controllers\RequestAnalyticsController;

Route::middleware(['web', 'auth', 'request-analytics.access'])
    ->get(config('request-analytics.route.pathname'), [RequestAnalyticsController::class, 'show'])
    ->name(config('request-analytics.route.name'));
