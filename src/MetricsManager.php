<?php

namespace STS\Metrics;

use Illuminate\Support\Manager;
use STS\Metrics\Drivers\CloudWatch;
use STS\Metrics\Drivers\InfluxDB;
use STS\Metrics\Drivers\NullDriver;

/**
 * Class MetricsManager
 * @package STS\Metrics
 */
class MetricsManager extends Manager
{
    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['metrics.default'] == null
            ? 'null'
            : $this->app['config']['metrics.default'];
    }

    /**
     * @return InfluxDB
     */
    public function createInfluxdbDriver()
    {
        return $this->app->make(InfluxDB::class);
    }

    /**
     * @return mixed
     */
    public function createCloudwatchDriver()
    {
        return $this->app->make(CloudWatch::class);
    }

    /**
     * @return mixed
     */
    public function createNullDriver()
    {
        return new NullDriver();
    }
}
