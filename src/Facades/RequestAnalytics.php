<?php

namespace MeShaon\RequestAnalytics\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MeShaon\RequestAnalytics\RequestAnalytics
 */
class RequestAnalytics extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \MeShaon\RequestAnalytics\RequestAnalytics::class;
    }
}
