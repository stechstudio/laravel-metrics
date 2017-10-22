<?php
namespace STS\EventMetrics;

/**
 * Class MetricsFacade
 * @package STS\EventMetrics
 */
class MetricsFacade extends \Illuminate\Support\Facades\Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return MetricsManager::class;
    }
}