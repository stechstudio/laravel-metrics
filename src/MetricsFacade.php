<?php
namespace STS\Metrics;

use Illuminate\Support\Facades\Facade;

/**
 * Class MetricsFacade
 * @package STS\EventMetrics
 */
class MetricsFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return MetricsManager::class;
    }
}