<?php

namespace STS\Metrics;

use Aws\Sdk;
use Illuminate\Support\ServiceProvider;
use InfluxDB\Client;
use STS\Metrics\Contracts\ShouldReportMetric;
use STS\Metrics\Drivers\CloudWatch;
use STS\Metrics\Drivers\InfluxDB;
use Illuminate\Foundation\Application as LaravelApplication;
use Laravel\Lumen\Application as LumenApplication;

/**
 * Class MetricsServiceProvider
 * @package STS\Metrics
 */
class MetricsServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(MetricsManager::class, function () {
            return $this->createManager();
        });

        $this->app->singleton(InfluxDB::class, function () {
            return $this->createInfluxDBDriver($this->app['config']['metrics.backends.influxdb']);
        });

        $this->app->singleton(CloudWatch::class, function () {
            return $this->createCloudWatchDriver($this->app['config']['metrics.backends.cloudwatch']);
        });
    }

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->setupConfig();

        $this->setupListener();
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [MetricsManager::class, InfluxDB::class, CloudWatch::class];
    }

    /**
     * Make sure we have config setup for Laravel and Lumen apps
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__ . '/../config/metrics.php');

        if ($this->app instanceof LaravelApplication) {
            $this->publishes([$source => config_path('metrics.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('metrics');
        }

        $this->mergeConfigFrom($source, 'metrics');
    }

    /**
     * Global event listener
     */
    protected function setupListener()
    {
        $this->app['events']->listen("*", function ($eventName, $payload) {
            $event = array_pop($payload);

            if (is_object($event) && $event instanceof ShouldReportMetric) {
                $this->app->make(MetricsManager::class)->driver()
                    ->add($event->createMetric());
            }
        });
    }

    /**
     * @return MetricsManager
     */
    protected function createManager()
    {
        $metrics = new MetricsManager($this->app);

        // Flush all queued metrics when PHP shuts down
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
                $config['tcp_port'],
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

    /**
     * Note this assumes you have AWS itself configured properly!
     *
     * @param array $config
     *
     * @return CloudWatch
     */
    protected function createCloudWatchDriver(array $config)
    {
        return new CloudWatch(
            app(Sdk::class)->createClient('cloudwatch'), $config['namespace']
        );
    }
}
