<?php

namespace STS\Metrics\Drivers;

use InfluxDB\Database;
use InfluxDB\Point;
use STS\Metrics\Adapters\AbstractInfluxDBAdapter;
use STS\Metrics\Metric;

/**
 * Class InfluxDB
 * @package STS\Metrics\Drivers
 */
class InfluxDB extends AbstractDriver
{   
    /**
     * @var array
     */
    protected $points = [];
    /**
     * @var AbstractInfluxDBAdapter
     */
    protected $adapter;

    /**
     * InfluxDB constructor.
     *
     * @param $tcpConnection
     * @param $udpConnection
     */
    public function __construct(AbstractInfluxDBAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Queue up a new measurement
     *
     * @param string $measurement the name of the measurement ... 'this-data'
     * @param mixed  $value       measurement value ... 15
     * @param array  $tags        measurement tags  ... ['host' => 'server01', 'region' => 'us-west']
     * @param array  $fields      measurement fields ... ['cpucount' => 10, 'free' => 2]
     * @param mixed  $timestamp   timestamp in nanoseconds on Linux ONLY
     *
     * @return $this
     */
    public function measurement($measurement, $value = null, array $tags = [], array $fields = [], $timestamp = null)
    {
        return $this->point(
            $this->adapter->point(
                $measurement,
                $value,
                array_merge($this->tags, $tags),
                array_merge($this->extra, $fields),
                $timestamp
            )
        );
    }

    /**
     * Queue up a new point
     *
     * @param Point $point
     *
     * @return $this
     */
    public function point(Point $point)
    {
        $this->points[] = $point;
        return $this;
    }

    /**
     * @return $this
     * @throws \InfluxDB\Exception
     */
    public function flush()
    {
        if (empty($this->getMetrics())) {
            return $this;
        }

        $this->send($this->getMetrics());
        $this->metrics = [];

        if (count($this->points)) {
            $this->adapter->writePoints($this->points);
            $this->points = [];
        }

        return $this;
    }

    /**
     * @param Metric $metric
     *
     * @return Point
     * @throws \InfluxDB\Database\Exception
     */
    public function format(Metric $metric)
    {
        return $this->adapter->point(
            $metric->getName(),
            $metric->getValue(),
            array_merge($this->tags, $metric->getTags()),
            array_merge($this->extra, $metric->getExtra()),
            $metric->getTimestamp()
        );
    }

    /**
     * Send one or more metrics to InfluxDB now
     *
     * @param $metrics
     *
     * @throws \InfluxDB\Exception
     */
    public function send($metrics)
    {
        $this->adapter->writePoints(
            array_map(function ($metric) {
                return $this->format($metric);
            }, (array)$metrics)
        );
    }

    /**
     * @return array
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @return Database
     */
    public function getWriteConnection()
    {
        return $this->adapter->getWriteConnection();
    }

    /**
     * Pass through to the Influx adapter anything we don't handle.
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->adapter->$method(...$parameters);
    }
}
