<?php
namespace STS\EventMetrics;

use Illuminate\Support\Manager;
use STS\EventMetrics\Drivers\InfluxDB;

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
        return strtolower($this->app['config']['metrics.default']);
    }

    /**
     * @return InfluxDB
     */
    public function createInfluxdbDriver()
    {
        return new InfluxDB(
            $this->app['config']['metrics.backends.influxdb.username'],
            $this->app['config']['metrics.backends.influxdb.password'],
            $this->app['config']['metrics.backends.influxdb.host'],
            $this->app['config']['metrics.backends.influxdb.database'],
            $this->app['config']['metrics.backends.influxdb.tcp_port'],
            $this->app['config']['metrics.backends.influxdb.udp_port']
        );
    }
}