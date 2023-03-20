<?php

namespace STS\Metrics;

use Aws\CloudWatch\CloudWatchClient;
use Aws\Sdk;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use InfluxDB\Client;
use STS\Metrics\Contracts\ShouldReportMetric;
use STS\Metrics\Drivers\CloudWatch;
use STS\Metrics\Drivers\InfluxDB;
use STS\Metrics\Drivers\InfluxDB2;
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

        $this->app->alias(MetricsManager::class, 'metrics');

        $this->app->singleton(InfluxDB::class, function () {
            return $this->createInfluxDBDriver($this->app['config']['metrics.backends.influxdb']);
        });

        $this->app->singleton(InfluxDB2::class, function () {
            return $this->createInfluxDB2Driver($this->app['config']['metrics.backends.influxdb2']);
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
        return ['metrics', MetricsManager::class, InfluxDB::class, CloudWatch::class];
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

        $udpConnection = (Arr::has($config, 'udp_port') && !empty($config['udp_port']))
            ? Client::fromDSN(sprintf('udp+influxdb://%s:%s@%s:%s/%s',
                Arr::get($config, 'username', 'default'), // Not required for UDP
                Arr::get($config, 'password', 'default'), // Not required for UDP
                $config['host'],
                $config['udp_port'],
                Arr::get($config, 'database', 'default') // Not required for UDP
            ))
            : null;

        return new InfluxDB($tcpConnection, $udpConnection);
    }

    protected function createInfluxDB2Driver(array $config)
    {
        return new InfluxDB2(
            new \InfluxDB2\Client($config)
        );
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
        $opts = [
            'region'  => Arr::get($config, 'region'),
            'version' => '2010-08-01',
        ];

        // Add credentials if they've been defined, else fallback to loading
        // credentials from the environment.
        $key = Arr::get($config, 'key');
        $secret = Arr::get($config, 'secret');
        if ($key !== null && $secret !== null) {
            $opts['credentials'] = [
                'key'    => $key,
                'secret' => $secret,
            ];
        }

        return new CloudWatch(new CloudWatchClient($opts), $config['namespace']);
    }
}
