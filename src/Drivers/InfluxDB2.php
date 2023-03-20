<?php

namespace STS\Metrics\Drivers;

use InfluxDB2\Point;
use InfluxDB2\WriteApi;
use STS\Metrics\Metric;
use STS\Metrics\Traits\ComputesNanosecondTimestamps;

/**
 * Class InfluxDB2
 * @package STS\Metrics\Drivers
 */
class InfluxDB2 extends AbstractDriver
{
    use ComputesNanosecondTimestamps;

    /**
     * @var \InfluxDB2\Client
     */
    protected $client;

    /**
     * @var \InfluxDB2\WriteApi
     */
    protected $writeConnection;

    protected $points;

    /**
     * InfluxDB2 constructor.
     *
     * @param \InfluxDB2\Client $client
     */
    public function __construct($client)
    {
        $this->client = $client;
        $this->writeConnection = $client->createWriteApi();
    }

    /**
     * Queue up a new measurement
     *
     * @param [type] $measurement
     * @param [type] $value
     * @param array $tags
     * @param array $fields
     * @param [type] $timestamp
     * @return void
     */
    public function measurement($measurement, $value = null, array $tags = [], array $fields = [], $timestamp = null)
    {
        return $this->point(new Point(
            $measurement,
            array_merge($this->tags, $tags),
            array_merge($this->extra, $fields, compact('value')),
            $this->getNanoSecondTimestamp($timestamp)
        ));
    }

    public function point(Point $point)
    {
        $this->points[] = $point;
        return $this;
    }

    public function flush()
    {
        if (empty($this->getMetrics())) {
            return $this;
        }

        $this->send($this->getMetrics());
        $this->metrics = [];

        if (count($this->points)) {
            $this->getWriteConnection()->write($this->points);
            $this->points = [];
        }

        return $this;
    }

    public function format(Metric $metric)
    {
        return new Point(
            $metric->getName(),
            array_merge($this->tags, $metric->getTags()),
            array_merge($this->extra, $metric->getExtra(), ['value' => $metric->getValue()]),
            $this->getNanoSecondTimestamp($metric->getTimestamp())
        );
    }

    public function send($metrics)
    {
        $this->getWriteConnection()->write(
            array_map(function ($metric) {
                return $this->format($metric);
            }, (array) $metrics)
        );
    }

    public function getPoints()
    {
        return $this->points;
    }

    public function getWriteConnection()
    {
        return $this->writeConnection;
    }

    public function setWriteConnection(WriteApi $writeConnection)
    {
        $this->writeConnection = $writeConnection;
    }
}
