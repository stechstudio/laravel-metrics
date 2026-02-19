<?php

namespace STS\Metrics;

use Illuminate\Support\Manager;
use STS\Metrics\Drivers\AbstractDriver;
use STS\Metrics\Drivers\CloudWatch;
use STS\Metrics\Drivers\InfluxDB;
use STS\Metrics\Drivers\LogDriver;
use STS\Metrics\Drivers\NullDriver;
use STS\Metrics\Drivers\PostHog;
use STS\Metrics\Drivers\PrometheusDriver;

/**
 * @mixin AbstractDriver
 */
class MetricsManager extends Manager
{
    protected $driverCreatedCallback = null;

    protected ?\Closure $userIdResolver = null;

    public function resolveUserIdWith(\Closure $resolver): static
    {
        $this->userIdResolver = $resolver;

        return $this;
    }

    public function whenDriverCreated(callable $callback)
    {
        $this->driverCreatedCallback = $callback;

        return $this;
    }

    public function getDefaultDriver(): string
    {
        return $this->container['config']['metrics.default'] == null
            ? 'null'
            : $this->container['config']['metrics.default'];
    }

    protected function createDriver($driver)
    {
        $driver = parent::createDriver($driver);

        if($this->userIdResolver) {
            $driver->resolveUserIdWith($this->userIdResolver);
        }

        if($this->driverCreatedCallback) {
            call_user_func($this->driverCreatedCallback, $driver);
        }

        return $driver;
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

    public function createPrometheusDriver(): PrometheusDriver
    {
        return $this->container->make(PrometheusDriver::class);
    }
}
