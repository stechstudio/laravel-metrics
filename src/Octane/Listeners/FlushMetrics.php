<?php

namespace STS\Metrics\Octane\Listeners;

class FlushMetrics
{
    public function handle($event)
    {
        $metrics = app('metrics');

        foreach ($metrics->getDrivers() as $driver) {
            $driver->flush();
        }
    }
}