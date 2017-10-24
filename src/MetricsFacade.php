<?php

namespace STS\Metrics;

use Illuminate\Support\Facades\Facade;
use STS\Metrics\Drivers\AbstractDriver;

/**
 * @method static mixed add(Metric $metric)
 * @method static AbstractDriver driver()
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
