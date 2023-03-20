<?php

namespace STS\Metrics;

use Illuminate\Support\Manager;
use STS\Metrics\Drivers\CloudWatch;
use STS\Metrics\Drivers\InfluxDB;
use STS\Metrics\Drivers\InfluxDB2;
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
        return $this->container['config']['metrics.default'] == null
            ? 'null'
            : $this->container['config']['metrics.default'];
    }

    /**
     * @return InfluxDB
     */
    public function createInfluxdbDriver()
    {
        return $this->container->make(InfluxDB::class);
    }

    /**
     * @return mixed
     */
    public function createCloudwatchDriver()
    {
        return $this->container->make(CloudWatch::class);
    }

    /**
     * @return mixed
     */
    public function createNullDriver()
    {
        return new NullDriver();
    }
}
