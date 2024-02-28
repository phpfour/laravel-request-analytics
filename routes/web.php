<?php

use Illuminate\Support\Facades\Route;
use MeShaon\RequestAnalytics\Controllers\RequestAnalyticsController;

Route::get('/analytics', [RequestAnalyticsController::class, 'show'])->name('request.analytics');
