<?php

namespace STS\Metrics;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed add(Metric $metric)
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
