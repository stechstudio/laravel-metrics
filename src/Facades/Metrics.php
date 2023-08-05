<?php

namespace STS\Metrics\Facades;

use Illuminate\Support\Facades\Facade;
use STS\Metrics\Drivers\AbstractDriver;
use STS\Metrics\Metric;
use STS\Metrics\MetricsManager;

/**
 * @mixin AbstractDriver
 */
class Metrics extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return MetricsManager::class;
    }
}
