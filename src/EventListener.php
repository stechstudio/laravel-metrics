<?php
namespace STS\EventMetrics;

use InfluxDB;

/**
 * Class EventListener
 * @package STS\EventMetrics
 */
class EventListener
{
    /**
     * @param $eventName
     * @param $payload
     *
     * @return bool
     */
    public function handle($eventName, $payload)
    {
        $event = array_pop($payload);

        if(!is_object($event) || !$event instanceof ShouldReportMetric) {
            return true;
        }

        InfluxDB::add(
            $event->getMetricName(),
            $event->getMetricValue(),
            $event->getMetricTags(),
            $event->getMetricFields(),
            $event->getMetricTimestamp()
        );
    }
}