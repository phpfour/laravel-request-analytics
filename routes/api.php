<?php

use Illuminate\Support\Facades\Route;
use MeShaon\RequestAnalytics\Controllers\Api\AnalyticsApiController;

Route::middleware(['api', 'auth:sanctum', 'request-analytics.access'])
    ->prefix('api/v1/analytics')
    ->name('request-analytics.api.')
    ->group(function () {
        Route::get('/overview', [AnalyticsApiController::class, 'overview'])->name('overview');
        Route::get('/visitors', [AnalyticsApiController::class, 'visitors'])->name('visitors');
        Route::get('/page-views', [AnalyticsApiController::class, 'pageViews'])->name('page-views');
    });
