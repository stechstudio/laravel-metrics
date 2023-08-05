<?php

namespace STS\Metrics;

use Illuminate\Support\Manager;
use STS\Metrics\Drivers\AbstractDriver;
use STS\Metrics\Drivers\CloudWatch;
use STS\Metrics\Drivers\InfluxDB;
use STS\Metrics\Drivers\LogDriver;
use STS\Metrics\Drivers\NullDriver;
use STS\Metrics\Drivers\PostHog;

/**
 * @mixin AbstractDriver
 */
class MetricsManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->container['config']['metrics.default'] == null
            ? 'null'
            : $this->container['config']['metrics.default'];
    }

    public function createInfluxdbDriver(): InfluxDB
    {
        return $this->container->make(InfluxDB::class);
    }

    public function createCloudwatchDriver(): CloudWatch
    {
        return $this->container->make(CloudWatch::class);
    }

    public function createPostHogDriver(): PostHog
    {
        return $this->container->make(PostHog::class);
    }

    public function createLogDriver(): LogDriver
    {
        return $this->container->make(LogDriver::class);
    }

    public function createNullDriver(): NullDriver
    {
        return new NullDriver();
    }
}
