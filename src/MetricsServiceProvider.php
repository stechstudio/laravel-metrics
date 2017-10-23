<?php

namespace STS\Metrics;

use Illuminate\Support\ServiceProvider;
use InfluxDB\Client;
use STS\Metrics\Contracts\ShouldReportMetric;
use STS\Metrics\Drivers\InfluxDB;

/**
 * Class MetricsServiceProvider
 * @package STS\Metrics
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
            return $this->createInfluxDBDriver($this->app['config']['metrics.backends.influxdb']);
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
    protected function createInfluxDBDriver(array $config)
    {
        $tcpConnection = Client::fromDSN(
            sprintf('influxdb://%s:%s@%s:%s/%s',
                $config['username'],
                $config['password'],
                $config['host'],
                array_get($config, 'tcp_port', 8086),
                $config['database']
            )
        );

        $udpConnection = array_has($config, 'udp_port')
            ? Client::fromDSN(sprintf('udp+influxdb://%s:%s@%s:%s/%s',
                $config['username'],
                $config['password'],
                $config['host'],
                $config['udp_port'],
                $config['database']
            ))
            : null;

        return new InfluxDB($tcpConnection, $udpConnection);
    }
}
