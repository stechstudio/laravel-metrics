<?php

namespace STS\EventMetrics;

use Illuminate\Support\ServiceProvider;

/**
 * Class EventMetricsServiceProvider
 * @package STS\EventMetrics
 */
class EventMetricsServiceProvider extends ServiceProvider
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

        $this->app->singleton(InfluxDB::class, function ($app) {
            return $this->createInfluxDBClient();
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

        $this->app['events']->listen("*", EventListener::class);
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [InfluxDB::class];
    }

    /**
     * @return InfluxDB
     */
    protected function createInfluxDBClient()
    {
        $client = new InfluxDB(
            config('metrics.username'),
            config('metrics.password'),
            config('metrics.host'),
            config('metrics.database'),
            config('metrics.tcp_port'),
            config('metrics.udp_port')
        );

        register_shutdown_function(function () use ($client) {
            $client->flush();
        });

        return $client;
    }
}