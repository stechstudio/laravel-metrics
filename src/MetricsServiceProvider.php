<?php

namespace STS\Metrics;

use Illuminate\Support\ServiceProvider;
use STS\Metrics\Contracts\ShouldReportMetric;
use STS\Metrics\Drivers\InfluxDB;

/**
 * Class MetricsServiceProvider
 * @package STS\EventMetrics
 */
class MetricsServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    /**
     *
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/metrics.php', 'metrics');

        $this->app->singleton(MetricsManager::class, function () {
            return $this->createManager();
        });

        $this->app->singleton(InfluxDB::class, function() {
            return $this->createInfluxDBDriver();
        });
    }

    /**
     *
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/metrics.php' => config_path('metrics.php'),
        ], 'config');

        $this->app['events']->listen("*", function($eventName, $payload) {
            $event = array_pop($payload);

            if(is_object($event) && $event instanceof ShouldReportMetric) {
                $this->app
                    ->make(MetricsManager::class)
                    ->driver()
                    ->add($event->createMetric());
            }
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [MetricsManager::class, InfluxDB::class];
    }

    /**
     * @return MetricsManager
     */
    protected function createManager()
    {
        $metrics = new MetricsManager($this->app);

        register_shutdown_function(function () use ($metrics) {
            foreach ($metrics->getDrivers() AS $driver) {
                $driver->flush();
            }
        });

        return $metrics;
    }

    /**
     * @return InfluxDB
     */
    protected function createInfluxDBDriver()
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