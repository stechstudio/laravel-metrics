<?php
namespace STS\Metrics;

use Illuminate\Support\Manager;
use STS\Metrics\Drivers\InfluxDB;

/**
 * Class MetricsManager
 * @package STS\EventMetrics
 */
class MetricsManager extends Manager
{
    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['metrics.default'];
    }

    /**
     * @return InfluxDB
     */
    public function createInfluxdbDriver()
    {
        return $this->app->make(InfluxDB::class);
    }
}