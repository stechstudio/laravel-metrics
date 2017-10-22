<?php

namespace STS\Metrics;

use Illuminate\Support\ServiceProvider;
use STS\Metrics\Contracts\ShouldReportMetric;

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

        $this->app->singleton(MetricsManager::class, function ($app) {
            $metrics = new MetricsManager($app);

            register_shutdown_function(function () use ($metrics) {
                foreach ($metrics->getDrivers() AS $driver) {
                    $driver->flush();
                }
            });

            return $metrics;
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
                    ->add($event->createMetric());
            }
        });
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [MetricsManager::class];
    }
}